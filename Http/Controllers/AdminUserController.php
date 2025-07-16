<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

use Jiny\Admin\Models\AdminUser;

class AdminUserController extends Controller
{
    private $route;
    private $filterable = [];
    private $validFilters = [];

    /**
     * 로깅 활성화
     */
    protected $activeLog = true;

    /**
     * 로그 테이블명
     */
    protected $logTableName = 'admin_users';

    public function __construct()
    {
        $this->filterable = [
            'name',
            'email',
            'type',
            'status',
            'is_verified',
        ];
        $this->validFilters = [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'is_verified' => 'boolean',
        ];

        $this->route = 'admin.admin.users.';
    }

    /**
     * 관리자 회원 목록 조회
     */
    public function index(Request $request): View
    {
        $query = AdminUser::query();

        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query);
        
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        $rows = $query->paginate(15);

        return view('jiny-admin::users.index', [
            'rows' => $rows,
            'filters' => $filters,
            'sort' => $sortField,
            'dir' => $sortDirection,
            'route' => $this->route,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 필터 적용
     */
    public function applyFilter($filters, $query)
    {
        // 부분 일치가 자연스러운 필드
        $likeFields = ['name', 'email', 'memo'];

        foreach ($this->filterable as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                if (in_array($column, $likeFields)) {
                    $query->where($column, 'like', "%{$filters[$column]}%");
                } else {
                    $query->where($column, $filters[$column]);
                }
            }
        }

        // 추가 필드: phone, login_count, memo
        if (isset($filters['phone']) && $filters['phone'] !== '') {
            $query->where('phone', 'like', "%{$filters['phone']}%");
        }
        if (isset($filters['login_count']) && $filters['login_count'] !== '') {
            $query->where('login_count', '>=', (int)$filters['login_count']);
        }
        if (isset($filters['memo']) && $filters['memo'] !== '') {
            $query->where('memo', 'like', "%{$filters['memo']}%");
        }

        // search는 or 조건
        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('type', 'like', "%{$filters['search']}%")
                  ->orWhere('status', 'like', "%{$filters['search']}%")
                  ;
            });
        }

        // 디버깅용 로그
        // \Log::debug('AdminUser filter', $filters);

        return $query;
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
     * 관리자 회원 생성 폼
     */
    public function create(): View
    {
        return view('jiny-admin::users.create', [
            'route' => $this->route,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 관리자 회원 저장
     */
    public function store(Request $request)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admin_users,email',
            'password' => 'required|string|min:6',
            'type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            'is_verified' => 'boolean',
        ];
        $data = $request->validate($validationRules);
        $data['password'] = bcrypt($data['password']);
        $data['is_verified'] = $request->has('is_verified');
        $user = AdminUser::create($data);
        // 로그 등 추가 필요시 여기에 작성
        return response()->json([
            'success' => true,
            'message' => '성공적으로 생성되었습니다.',
            'user' => $user
        ]);
    }

    /**
     * 관리자 회원 상세 조회
     */
    public function show($id): View
    {
        $user = AdminUser::findOrFail($id);
        return view('jiny-admin::users.show', [
            'route' => $this->route,
            'user' => $user,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 관리자 회원 수정 폼
     */
    public function edit($id): View
    {
        $user = AdminUser::findOrFail($id);
        return view('jiny-admin::users.edit', [
            'route' => $this->route,
            'user' => $user,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 관리자 회원 업데이트
     */
    public function update(Request $request, $id)
    {
        $user = AdminUser::findOrFail($id);
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admin_users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            'is_verified' => 'boolean',
        ];
        $data = $request->validate($validationRules);
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        $data['is_verified'] = $request->has('is_verified');
        $user->update($data);
        // 로그 등 추가 필요시 여기에 작성
        return response()->json([
            'success' => true,
            'message' => '성공적으로 수정되었습니다.',
            'user' => $user
        ]);
    }

    /**
     * 관리자 회원 삭제
     */
    public function destroy($id)
    {
        $user = AdminUser::findOrFail($id);
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => '성공적으로 삭제되었습니다.'
        ]);
    }

    /**
     * 선택 삭제 (bulk delete)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        if (!is_array($request->input('ids'))) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 입력입니다.'
            ], 422);
        }
        $ids = array_map('intval', $request->input('ids'));
        $deletedCount = AdminUser::whereIn('id', $ids)->delete();
        return response()->json([
            'success' => true,
            'message' => "{$deletedCount}명의 관리자가 성공적으로 삭제되었습니다."
        ]);
    }
}
