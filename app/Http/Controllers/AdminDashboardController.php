<?php
namespace Jiny\Admin\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminActivityLog;
use Jiny\Admin\App\Models\AdminAuditLog;

/**
 * AdminDashboardController
 * 
 * 대시보드 전용 컨트롤러
 * 각 모듈의 대시보드 페이지를 처리하기 위한 기본 구조를 제공
 * 
 * @package Jiny\Admin\Http\Controllers
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 */
abstract class AdminDashboardController extends Controller
{
    protected $tableName;
    protected $moduleName;

    public function __construct()
    {
        $this->tableName = $this->getTableName();
        $this->moduleName = $this->getModuleName();
    }

    /**
     * 대시보드 메인 페이지
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 라우트 이름 추출
        $route = $this->getRouteName($request);

        $view = $this->_index($request);
        
        // Activity Log 기록
        $this->logActivity('read', '대시보드 접근', null, null);
        
        // 템플릿에서 반환받은 뷰에 라우트 이름 추가
        return $view->with('route', $route);
    }

    /**
     * 대시보드 데이터를 처리하는 추상 메서드
     * 자식 클래스에서 구현해야 함
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    protected function _index(Request $request)
    {
        throw new \Exception('_index method must be implemented');
    }

    /**
     * 테이블명 반환
     * 자식 클래스에서 구현해야 함
     * 
     * @return string
     */
    protected function getTableName()
    {
        throw new \Exception('getTableName method must be implemented');
    }

    /**
     * 모듈명 반환
     * 자식 클래스에서 구현해야 함
     * 
     * @return string
     */
    protected function getModuleName()
    {
        throw new \Exception('getModuleName method must be implemented');
    }

    /**
     * Activity Log 기록
     * 
     * @param string $action
     * @param string $description
     * @param int|null $targetId
     * @param array|null $data
     */
    protected function logActivity($action, $description, $targetId = null, $data = null)
    {
        try {
            $adminId = $this->getAdminId();
            
            // 디버깅 로그 추가
            \Log::info('Dashboard Activity Log Debug', [
                'action' => $action,
                'admin_id' => $adminId,
                'admin_guard_check' => Auth::guard('admin')->check(),
                'default_auth_check' => Auth::check(),
                'current_user_email' => Auth::check() ? Auth::user()->email : null,
                'route' => request()->route()->getName(),
            ]);
            
            if (!$adminId) {
                \Log::warning('Admin ID not found for dashboard activity log', [
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
                    'dashboard_type' => 'main'
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard activity log creation failed', [
                'error' => $e->getMessage(),
                'action' => $action,
                'admin_id' => $adminId ?? null,
            ]);
        }
    }

    /**
     * 관리자 ID 가져오기
     * 
     * @return int|null
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
     * 
     * @param string $action
     * @return string
     */
    protected function getSeverityForAction($action)
    {
        $severityMap = [
            'create' => 'medium',
            'update' => 'medium',
            'delete' => 'high',
            'read' => 'low',
            'edit' => 'low',
            'dashboard' => 'low',
        ];

        return $severityMap[$action] ?? 'medium';
    }

    /**
     * 라우트 이름 추출
     * 
     * @param Request $request
     * @return string
     */
    protected function getRouteName(Request $request)
    {
        $route = $request->route()->getName();
        $route = substr($route, 0, strrpos($route, '.')).".";
        return $route;
    }

    /**
     * 대시보드 통계 데이터를 안전하게 가져오는 헬퍼 메서드
     * 
     * @param callable $callback
     * @param mixed $default
     * @return mixed
     */
    protected function safeGetData(callable $callback, $default = null)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            \Log::error('Dashboard data retrieval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $default;
        }
    }

    /**
     * 대시보드용 기본 변수들을 초기화하는 헬퍼 메서드
     * 
     * @return array
     */
    protected function getDefaultDashboardData()
    {
        return [
            'stats' => [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'percentage' => 0,
                'today_count' => 0,
                'week_count' => 0,
                'month_count' => 0,
            ],
            'recent_items' => collect(),
            'chart_data' => [
                'labels' => [],
                'datasets' => []
            ],
            'quick_actions' => [],
            'alerts' => [],
            'loading' => false,
            'error' => null
        ];
    }
}
