<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Jiny\Admin\App\Models\AdminActivityLog;
use Jiny\Admin\App\Models\AdminAuditLog;
use Jiny\Admin\App\Models\AdminUser;

class AdminSessionController extends AdminResourceController
{
    protected $sortableColumns = ['session_id', 'admin_name', 'admin_email', 'admin_type', 'ip_address', 'last_activity', 'login_at'];
    protected $filterable = ['search', 'type', 'active', 'date_from', 'date_to'];
    private $route = 'admin.sessions.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 테이블 이름 반환
     */
    protected function getTableName()
    {
        return 'sessions';
    }

    /**
     * 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin_sessions';
    }

    /**
     * 세션 목록 (추상 메서드 구현)
     */
    protected function _index(Request $request): View
    {
        // 1. admin_sessions 테이블에서 활성 세션만 조회 (중복 방지)
        $adminSessionRows = DB::table('admin_sessions')
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get();

        $adminSessions = [];
        foreach ($adminSessionRows as $adminSession) {
            // 2. sessions 테이블에서 해당 세션 정보 조회
            $session = DB::table('sessions')->where('id', $adminSession->session_id)->first();
            
            if ($session) {
                // 3. 세션 만료 확인 (기본 120분)
                $sessionLifetime = config('session.lifetime', 120);
                
                // last_activity 시간 처리 개선
                $lastActivity = null;
                if ($adminSession->last_activity) {
                    $lastActivity = $adminSession->last_activity;
                } elseif ($session->last_activity) {
                    // sessions 테이블의 last_activity는 Unix timestamp
                    $lastActivity = \Carbon\Carbon::createFromTimestamp($session->last_activity);
                } else {
                    // 기본값으로 현재 시간 사용
                    $lastActivity = now();
                }
                
                // Carbon 객체가 아닌 경우 변환
                if (!$lastActivity instanceof \Carbon\Carbon) {
                    $lastActivity = \Carbon\Carbon::parse($lastActivity);
                }
                
                // 원본 last_activity 보존을 위해 복사본 사용
                $lastActivityCopy = $lastActivity->copy();
                $expiryTime = $lastActivityCopy->addMinutes($sessionLifetime);
                
                // 만료된 세션은 제외
                if (now()->isAfter($expiryTime)) {
                    // 만료된 세션을 비활성으로 표시
                    DB::table('admin_sessions')
                        ->where('session_id', $adminSession->session_id)
                        ->update(['is_active' => false]);
                    continue;
                }

                $adminSessions[$adminSession->session_id] = [
                    'session_id' => $adminSession->session_id,
                    'admin_user_id' => $adminSession->admin_user_id,
                    'admin_name' => $adminSession->admin_name,
                    'admin_email' => $adminSession->admin_email,
                    'admin_type' => $adminSession->admin_type,
                    'ip_address' => $adminSession->ip_address ?? $session->ip_address,
                    'user_agent' => $adminSession->user_agent ?? $session->user_agent,
                    'last_activity' => $lastActivity, // 원본 시간 보존
                    'last_activity_formatted' => $this->formatKoreanTime($lastActivity), // 포맷팅된 시간
                    'login_location' => $adminSession->login_location,
                    'device' => $adminSession->device,
                    'login_at' => $adminSession->login_at,
                    'is_active' => $adminSession->is_active,
                ];
            }
        }

        // 4. 사용자별 중복 세션 제거 (가장 최근 세션만 유지)
        $adminSessions = $this->deduplicateSessionsByUser($adminSessions);

        // 5. 필터 적용
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
        
        // 6. 정렬 적용
        $sortField = $request->get('sort', 'last_activity');
        $sortDirection = $request->get('direction', 'desc');
        usort($filtered, function($a, $b) use ($sortField, $sortDirection) {
            $aVal = $a[$sortField] ?? null;
            $bVal = $b[$sortField] ?? null;
            
            // Carbon 객체 비교
            if ($aVal instanceof \Carbon\Carbon && $bVal instanceof \Carbon\Carbon) {
                if ($sortDirection === 'asc') {
                    return $aVal->compare($bVal);
                } else {
                    return $bVal->compare($aVal);
                }
            }
            
            // 일반 값 비교
            if ($aVal == $bVal) return 0;
            if ($sortDirection === 'asc') {
                return $aVal <=> $bVal;
            } else {
                return $bVal <=> $aVal;
            }
        });
        
        // 7. 페이징 (수동)
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);
        $items = array_values($filtered);
        $total = count($items);
        $paged = array_slice($items, ($page-1)*$perPage, $perPage);
        $sessions = new LengthAwarePaginator($paged, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        // 통계 데이터
        $stats = [
            'total' => count($adminSessions),
            'active' => count(array_filter($adminSessions, fn($s) => isset($s['is_active']) && $s['is_active'])),
            'inactive' => count(array_filter($adminSessions, fn($s) => isset($s['is_active']) && !$s['is_active'])),
            'super' => count(array_filter($adminSessions, fn($s) => isset($s['admin_type']) && $s['admin_type'] === 'super')),
        ];

        $sort = $request->get('sort', 'last_activity');
        $dir = $request->get('direction', 'desc');
        
        $filters = [
            'search' => $filterSearch,
            'type' => $filterType,
            'active' => $filterActive,
        ];
        
        return view('jiny-admin::admin.sessions.index', [
            'rows' => $sessions,
            'stats' => $stats,
            'sort' => $sort,
            'dir' => $dir,
            'filters' => $filters,
            'route' => $this->route
        ]);
    }

    /**
     * 세션 상세 조회 (추상 메서드 구현)
     */
    protected function _show(Request $request, $id): View
    {
        // admin_sessions 테이블에서 세션 정보 조회
        $adminSession = DB::table('admin_sessions')->where('session_id', $id)->first();
        if (!$adminSession) {
            abort(404, '세션을 찾을 수 없습니다.');
        }
        
        // sessions 테이블에서 세션 정보 조회
        $session = DB::table('sessions')->where('id', $id)->first();
        
        // 관리자 정보 조회
        $adminUser = null;
        if ($adminSession->admin_user_id) {
            $adminUser = AdminUser::find($adminSession->admin_user_id);
        }
        
        // 시간 포맷팅
        $lastActivity = null;
        if ($adminSession->last_activity) {
            $lastActivity = $adminSession->last_activity;
        } elseif ($session && $session->last_activity) {
            $lastActivity = \Carbon\Carbon::createFromTimestamp($session->last_activity);
        }
        
        $lastActivityFormatted = $lastActivity ? $this->formatKoreanTime($lastActivity) : '알 수 없음';
        
        return view('jiny-admin::admin.sessions.show', [
            'session' => $session,
            'adminSession' => $adminSession,
            'adminUser' => $adminUser,
            'lastActivityFormatted' => $lastActivityFormatted,
            'route' => $this->route
        ]);
    }

    // /**
    //  * 세션 상세 조회 (public 메서드)
    //  */
    // public function show(Request $request, $id): View
    // {
    //     return $this->_show($request, $id);
    // }

    /**
     * 세션 강제 종료
     */
    /**
     * 삭제 확인 폼 제공
     */
    public function confirm($id)
    {
        $session = DB::table('admin_sessions')->where('session_id', $id)->first();
        
        if (!$session) {
            return response()->json(['error' => '세션을 찾을 수 없습니다.'], 404);
        }
        
        $url = route('admin.sessions.destroy', $id);
        $title = $session->admin_name . ' 세션 강제 종료';
        
        // AJAX 요청인 경우 HTML만 반환
        if (request()->ajax()) {
            return view('jiny-admin::admin.sessions.form_delete', [
                'session' => $session,
                'url' => $url,
                'title' => $title,
                'randomKey' => strtoupper(substr(md5(uniqid()), 0, 8))
            ]);
        }
        
        // 일반 요청인 경우 전체 페이지 반환
        return view('jiny-admin::admin.sessions.form_delete', [
            'session' => $session,
            'url' => $url,
            'title' => $title,
            'randomKey' => strtoupper(substr(md5(uniqid()), 0, 8))
        ]);
    }

    /**
     * 세션 강제 종료
     */
    public function destroy($id)
    {
        try {
            // 삭제 전 데이터 가져오기 (Audit Log용)
            $session = DB::table('admin_sessions')->where('session_id', $id)->first();
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => '세션을 찾을 수 없습니다.'
                ], 404);
            }
            
            $oldData = (array)$session;
            
            // 세션 데이터 삭제
            DB::table('sessions')->where('id', $id)->delete();
            DB::table('admin_sessions')->where('session_id', $id)->delete();
            
            // Activity Log 기록
            $this->logActivity('delete', '세션 강제 종료', $oldData, ['deleted_session_id' => $id]);
            
            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '세션 강제 종료', null);
            
            return response()->json([
                'success' => true,
                'message' => '세션이 강제 종료되었습니다.',
                'data' => [
                    'session_id' => $id,
                    'admin_name' => $oldData['admin_name'] ?? 'Unknown'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '세션 강제 종료 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
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
            'module' => 'sessions',
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

    /**
     * 사용자별 중복 세션 제거 (가장 최근 세션만 유지)
     * 
     * @param array $sessions
     * @return array
     */
    private function deduplicateSessionsByUser(array $sessions): array
    {
        $userSessions = [];
        
        foreach ($sessions as $sessionId => $session) {
            $userId = $session['admin_user_id'];
            
            // 해당 사용자의 기존 세션이 없거나, 현재 세션이 더 최근인 경우
            if (!isset($userSessions[$userId]) || 
                $session['last_activity'] > $userSessions[$userId]['last_activity']) {
                $userSessions[$userId] = $session;
            }
        }
        
        // 중복 제거된 세션들을 다시 session_id를 키로 하는 배열로 변환
        $deduplicated = [];
        foreach ($userSessions as $session) {
            $deduplicated[$session['session_id']] = $session;
        }
        
        return $deduplicated;
    }

    /**
     * 일괄 세션 강제 종료
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'string',
        ]);

        $ids = $request->ids;
        $count = count($ids);

        // 삭제 전 데이터 가져오기 (Audit Log용)
        $oldData = DB::table('admin_sessions')->whereIn('session_id', $ids)->get()->toArray();

        // 세션 데이터 삭제
        DB::table('sessions')->whereIn('id', $ids)->delete();
        DB::table('admin_sessions')->whereIn('session_id', $ids)->delete();

        // Activity Log 기록
        $this->logActivity('delete', '세션 일괄 강제 종료', null, ['deleted_session_ids' => $ids]);
        
        // Audit Log 기록
        $this->logAudit('delete', $oldData, null, '세션 일괄 강제 종료', null);

        return response()->json([
            'success' => true,
            'message' => "{$count}개의 세션이 성공적으로 강제 종료되었습니다.",
        ]);
    }

    /**
     * 한글 시간 표현으로 변환
     * 
     * @param mixed $carbon
     * @return string
     */
    private function formatKoreanTime($carbon): string
    {
        if (!$carbon instanceof \Carbon\Carbon) {
            try {
                $carbon = \Carbon\Carbon::parse($carbon);
            } catch (\Exception $e) {
                return '알 수 없음';
            }
        }

        $now = \Carbon\Carbon::now();

        // 미래 시간인 경우
        if ($carbon->isAfter($now)) {
            return '방금 전';
        }

        $diff = $carbon->diffInSeconds($now);

        if ($diff < 60) {
            return '방금 전';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . '분 전';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . '시간 전';
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . '일 전';
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . '개월 전';
        } else {
            $years = floor($diff / 31536000);
            return $years . '년 전';
        }
    }
} 