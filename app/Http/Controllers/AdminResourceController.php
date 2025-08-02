<?php
namespace Jiny\Admin\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminActivityLog;
use Jiny\Admin\App\Models\AdminAuditLog;

/**
 * AdminResourceController
 * this class is used to handle the admin resource requests
 * @package Jiny\Admin\Http\Controllers
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 */
abstract class AdminResourceController extends Controller
{


    protected $filterable = [];
    protected $validFilters = [];

    protected $tableName;
    protected $moduleName;

    public function __construct()
    {
        $this->tableName = $this->getTableName();
        $this->moduleName = $this->getModuleName();
    }

    // // 추상 메서드들 - 자식 클래스에서 구현해야 함
    // protected function _index(Request $request) { throw new \Exception('_index method must be implemented'); }
    // protected function _create(Request $request) { throw new \Exception('_create method must be implemented'); }
    // protected function _edit(Request $request, $id) { throw new \Exception('_edit method must be implemented'); }
    // protected function _store(Request $request) { throw new \Exception('_store method must be implemented'); }
    // protected function _update(Request $request, $id) { throw new \Exception('_update method must be implemented'); }
    // protected function _destroy(Request $request) { throw new \Exception('_destroy method must be implemented'); }


    public function index(Request $request)
    {
        // 라우트 이름 추출
        $route = $this->getRouteName($request);

        $view = $this->_index($request);
        
        // Activity Log 기록
        $this->logActivity('read', '목록 조회', null, null);
        
        // 템플릿에서 반환받은 뷰에 라우트 이름 추가
        // 템플릿에서 라우트 이름을 사용하여 라우트 링크를 동적으로 생성할 수 있습니다.
        return $view->with('route', $route);
    }

    protected function sort($query, $request)
    {
        // 정렬
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('direction', 'desc');

        if (in_array($sortBy, $this->sortableColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }


    public function create(Request $request)
    {
        $route = $this->getRouteName($request);
        $view = $this->_create($request);
        
        // Activity Log 기록
        $this->logActivity('create', '생성 폼 접근', null, null);
        
        // 템플릿에서 반환받은 뷰에 라우트 이름 추가
        // 템플릿에서 라우트 이름을 사용하여 라우트 링크를 동적으로 생성할 수 있습니다.
        return $view->with('route', $route);
    }

    public function show(Request $request, $id)
    {
        $route = $this->getRouteName($request);
        $view = $this->_show($request, $id);
        
        // Activity Log 기록
        $this->logActivity('show', '상세 조회', $id, null);
        
        // 템플릿에서 반환받은 뷰에 라우트 이름 추가
        // 템플릿에서 라우트 이름을 사용하여 라우트 링크를 동적으로 생성할 수 있습니다.
        return $view->with('route', $route);
    }

    public function edit(Request $request, $id)
    {
        $route = $this->getRouteName($request);
        $view = $this->_edit($request, $id);
        
        // Activity Log 기록
        $this->logActivity('edit', '수정 폼 접근', $id, null);
        
        // 템플릿에서 반환받은 뷰에 라우트 이름 추가
        // 템플릿에서 라우트 이름을 사용하여 라우트 링크를 동적으로 생성할 수 있습니다.
        return $view->with('route', $route);
    }

    public function store(Request $request)
    {
        $result = $this->_store($request);
        
        // Activity Log 기록
        $this->logActivity('create', '새 항목 생성', null, $request->all());
        
        // Audit Log 기록
        $this->logAudit('create', null, $request->all(), '새 항목이 생성되었습니다.');
        
        return $result;
    }

    public function update(Request $request, $id)
    {
        // 수정 전 데이터 가져오기 (Audit Log용)
        $oldData = $this->getOldData($id);
        
        $result = $this->_update($request, $id);
        
        // Activity Log 기록
        $this->logActivity('update', '항목 수정', $id, $request->all());
        
        // Audit Log 기록
        $this->logAudit('update', $oldData, $request->all(), '항목이 수정되었습니다.', $id);
        
        return $result;
    }

    public function destroy(Request $request)
    {
        // 삭제 전 데이터 가져오기 (Audit Log용)
        $id = $request->get('id') ?? $request->route('id');
        $oldData = $this->getOldData($id);
        
        $result = $this->_destroy($request);
        
        // Activity Log 기록
        $this->logActivity('delete', '항목 삭제', $id, null);
        
        // Audit Log 기록
        $this->logAudit('delete', $oldData, null, '항목이 삭제되었습니다.', $id);
        
        return $result;
    }

    /**
     * Activity Log 기록
     */
    protected function logActivity($action, $description, $targetId = null, $data = null)
    {
        try {
            $adminId = $this->getAdminId();
            
            // 디버깅 로그 추가
            \Log::info('Activity Log Debug', [
                'action' => $action,
                'admin_id' => $adminId,
                'admin_guard_check' => Auth::guard('admin')->check(),
                'default_auth_check' => Auth::check(),
                'current_user_email' => Auth::check() ? Auth::user()->email : null,
                'route' => request()->route()->getName(),
            ]);
            
            if (!$adminId) {
                \Log::warning('Admin ID not found for activity log', [
                    'action' => $action,
                    'route' => request()->route()->getName(),
                ]);
                return;
            }

            AdminActivityLog::create([
                'admin_user_id' => $adminId,
                'action' => $action,
                'module' => $this->moduleName,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'target_type' => $this->tableName,
                'target_id' => $targetId,
                'old_values' => null,
                'new_values' => $data,
                'severity' => $this->getSeverityForAction($action),
                'metadata' => [
                    'route' => request()->route()->getName(),
                    'method' => request()->method(),
                    'url' => request()->url(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Activity log creation failed', [
                'error' => $e->getMessage(),
                'action' => $action,
                'admin_id' => $adminId ?? null,
            ]);
        }
    }

    /**
     * Audit Log 기록
     */
    protected function logAudit($action, $oldValues = null, $newValues = null, $description = null, $recordId = null)
    {
        try {
            $adminId = $this->getAdminId();
            
            // 디버깅 로그 추가
            \Log::info('Audit Log Debug', [
                'action' => $action,
                'admin_id' => $adminId,
                'admin_guard_check' => Auth::guard('admin')->check(),
                'default_auth_check' => Auth::check(),
                'current_user_email' => Auth::check() ? Auth::user()->email : null,
                'route' => request()->route()->getName(),
            ]);
            
            if (!$adminId) {
                \Log::warning('Admin ID not found for audit log', [
                    'action' => $action,
                    'route' => request()->route()->getName(),
                ]);
                return;
            }

            AdminAuditLog::create([
                'admin_id' => $adminId,
                'action' => $action,
                'table_name' => $this->tableName,
                'record_id' => $recordId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'description' => $description,
                'severity' => $this->getSeverityForAction($action),
                'affected_count' => 1,
                'metadata' => [
                    'route' => request()->route()->getName(),
                    'method' => request()->method(),
                    'url' => request()->url(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Audit log creation failed', [
                'error' => $e->getMessage(),
                'action' => $action,
                'table_name' => $this->tableName,
                'admin_id' => $adminId ?? null,
            ]);
        }
    }

    /**
     * 관리자 ID 가져오기
     */
    protected function getAdminId()
    {
        // admin guard 사용 방식
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->id();
        }
        
        // 기본 Auth + admin_users 체크 방식
        if (Auth::check()) {
            $user = Auth::user();
            $admin = AdminUser::where('email', $user->email)->first();
            if ($admin) {
                return $admin->id;
            }
        }
        
        return null;
    }


    /**
     * 액션별 심각도 결정
     */
    protected function getSeverityForAction($action)
    {
        $severityMap = [
            'create' => 'medium',
            'update' => 'medium',
            'delete' => 'high',
            'read' => 'low',
            'edit' => 'low',
        ];

        return $severityMap[$action] ?? 'medium';
    }


    /**
     * 라우트 이름 추출
     */
    protected function getRouteName(Request $request)
    {
        $route = $request->route()->getName();
        $route = substr($route, 0, strrpos($route, '.')).".";
        return $route;
    }

    /**
     * 리퀘스트에서 filter_ 접두사가 붙은 파라미터를 추출합니다.
     */
    protected function getFilterParameters(Request $request)
    {
        $filters = [];
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter_') && !empty($value)) {
                $filters[substr($key, 7)] = $value;
            }
        }
        return $filters;
    }

    /**
     * 필터 적용
     */
    protected function applyFilter($filters, $query, $likeFields)
    {
        foreach ($this->filterable as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                if (in_array($column, $likeFields)) {
                    $query->where($column, 'like', "%{$filters[$column]}%");
                } else {
                    $query->where($column, $filters[$column]);
                }
            }
        }

        // search는 or 조건
        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where(function($q) use ($filters, $likeFields) {
                $first = true;
                foreach($likeFields as $field) {
                    if ($first) {
                        $q->where($field, 'like', "%{$filters['search']}%");
                        $first = false;
                    } else {
                        $q->orWhere($field, 'like', "%{$filters['search']}%");
                    }
                }
            });
        }

        return $query;
    }

}