<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Jiny\Admin\Models\AdminActivityLog;
use Jiny\Admin\Models\AdminAuditLog;
use Jiny\Admin\Models\AdminUser;

class AdminSessionController extends Controller
{
    private $route = 'admin.sessions';

    /**
     * 모든 세션 목록 출력 (sessions + admin_sessions 병합, sort/filter 지원)
     */
    public function index(Request $request)
    {
        // 1. sessions 테이블의 모든 세션 추출
        $rawSessions = DB::table('sessions')->get();
        $adminSessions = [];
        foreach ($rawSessions as $sess) {
            $adminSessions[$sess->id] = [
                'session_id' => $sess->id,
                'admin_user_id' => $sess->user_id,
                'ip_address' => $sess->ip_address,
                'user_agent' => $sess->user_agent,
                'last_activity' => $sess->last_activity,
            ];
        }
        // 2. admin_sessions 테이블과 left join
        $adminSessionRows = DB::table('admin_sessions')->whereIn('session_id', array_keys($adminSessions))->get()->keyBy('session_id');
        // 3. 정보 병합
        foreach ($adminSessions as $sid => &$sess) {
            if (isset($adminSessionRows[$sid])) {
                $row = $adminSessionRows[$sid];
                $sess['admin_name'] = $row->admin_name;
                $sess['admin_email'] = $row->admin_email;
                $sess['admin_type'] = $row->admin_type;
                $sess['login_location'] = $row->login_location;
                $sess['device'] = $row->device;
                $sess['login_at'] = $row->login_at;
                $sess['is_active'] = $row->is_active;
            } else if (!empty($sess['admin_user_id'])) {
                // admin_sessions에 없지만, admin_user_id가 있으면 admin_users 테이블에서 정보 보완
                $admin = \Jiny\Admin\Models\AdminUser::find($sess['admin_user_id']);
                if ($admin) {
                    $sess['admin_name'] = $admin->name;
                    $sess['admin_email'] = $admin->email;
                    $sess['admin_type'] = $admin->type;
                }
            }
        }
        // 4. 필터 적용
        $filterSearch = $request->get('filter_search');
        $filterType = $request->get('filter_type');
        $filterActive = $request->get('filter_active');
        $filtered = array_filter($adminSessions, function($sess) use ($filterSearch, $filterType, $filterActive) {
            $ok = true;
            if ($filterSearch) {
                $ok = $ok && (
                    (isset($sess['admin_name']) && str_contains($sess['admin_name'], $filterSearch)) ||
                    (isset($sess['admin_email']) && str_contains($sess['admin_email'], $filterSearch)) ||
                    (isset($sess['ip_address']) && str_contains($sess['ip_address'], $filterSearch))
                );
            }
            if ($filterType) {
                $ok = $ok && (isset($sess['admin_type']) && $sess['admin_type'] === $filterType);
            }
            if ($filterActive !== null && $filterActive !== '') {
                $ok = $ok && (isset($sess['is_active']) && (string)$sess['is_active'] === $filterActive);
            }
            return $ok;
        });
        // 5. 정렬 적용
        $sortField = $request->get('sort', 'last_activity');
        $sortDirection = $request->get('direction', 'desc');
        usort($filtered, function($a, $b) use ($sortField, $sortDirection) {
            $aVal = $a[$sortField] ?? null;
            $bVal = $b[$sortField] ?? null;
            if ($aVal == $bVal) return 0;
            if ($sortDirection === 'asc') {
                return $aVal <=> $bVal;
            } else {
                return $bVal <=> $aVal;
            }
        });
        // 6. 페이징 (수동)
        $page = $request->get('page', 1);
        $perPage = 20;
        $items = array_values($filtered);
        $total = count($items);
        $paged = array_slice($items, ($page-1)*$perPage, $perPage);
        $sessions = new LengthAwarePaginator($paged, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
        return view('jiny-admin::login_logs.index', [
            'route' => $this->route,
            'rows' => $sessions
        ]);
    }

    public function destroy($id)
    {
        DB::table('sessions')->where('id', $id)->delete();
        DB::table('admin_sessions')->where('session_id', $id)->delete();
        
        // 로그 기록
        $this->logSessionAction('destroy', $id, '세션 강제 종료');
        return redirect()->back()->with('success', '세션이 강제 종료되었습니다.');
    }

    /**
     * 세션 재발급(갱신) 및 로그 기록
     */
    public function refresh($id)
    {
        // 1. 기존 세션 데이터 조회
        $oldSession = DB::table('sessions')->where('id', $id)->first();
        if (!$oldSession) {
            return redirect()->back()->with('error', '세션을 찾을 수 없습니다.');
        }
        // 2. 새 세션ID 생성
        $newId = Str::random(40);
        // 3. 세션 데이터 복사 (ID만 변경)
        $newSession = (array)$oldSession;
        $newSession['id'] = $newId;
        $now = now();
        $newSession['last_activity'] = $now->timestamp;
        // 4. DB에 새 세션 insert, 기존 세션 delete
        DB::table('sessions')->insert($newSession);
        DB::table('sessions')->where('id', $id)->delete();
        // 5. admin_sessions도 ID 변경 및 last_activity 갱신
        $adminSession = DB::table('admin_sessions')->where('session_id', $id)->first();
        if ($adminSession) {
            $adminSessionArr = (array)$adminSession;
            $adminSessionArr['session_id'] = $newId;
            $adminSessionArr['last_activity'] = $now;
            unset($adminSessionArr['id']); // auto-increment 컬럼 제거
            // admin_user_id가 없으면 세션에서 보완
            if (empty($adminSessionArr['admin_user_id']) && !empty($oldSession->user_id)) {
                $adminSessionArr['admin_user_id'] = $oldSession->user_id;
            }
            DB::table('admin_sessions')->insert($adminSessionArr);
            DB::table('admin_sessions')->where('session_id', $id)->delete();
        }
        // 6. 로그 기록 (activity, audit)
        $this->logSessionAction('refresh', $newId, '세션 재발급(갱신)');
        
        return redirect()->back()->with('success', '세션이 재발급(갱신)되었습니다.');
    }

    /**
     * 세션 관련 액션을 activity-logs, audit-logs에 모두 기록
     */
    protected function logSessionAction($action, $sessionId, $desc)
    {
        $ip = request()->ip();
        $userAgent = request()->userAgent();
        $adminSession = DB::table('admin_sessions')->where('session_id', $sessionId)->first();
        $adminUserId = $adminSession->admin_user_id ?? null;
        // admin_user_id가 없으면 로그 기록하지 않음
        if (!$adminUserId) {
            return;
        }
        // activity log
        AdminActivityLog::create([
            'admin_user_id' => $adminUserId,
            'action' => $action,
            'description' => $desc,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
        // audit log
        AdminAuditLog::create([
            'admin_user_id' => $adminUserId,
            'action' => $action,
            'table_name' => 'sessions',
            'record_id' => $sessionId,
            'old_values' => null,
            'new_values' => null,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'description' => $desc,
            'severity' => 'medium',
            'affected_count' => 1,
        ]);
    }
} 