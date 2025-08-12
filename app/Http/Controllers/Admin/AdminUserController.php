<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Jiny\Admin\App\Models\AdminUser;

class AdminUserController extends AdminResourceController
{
    protected $sortableColumns = ['id', 'name', 'email', 'type', 'status', 'last_login_at', 'login_count', 'created_at'];
    protected $filterable = ['search', 'type', 'status', 'date_from', 'date_to'];
    private $config;

    public function __construct()
    {
        parent::__construct();
        
        // 패키지의 admin config 읽어오기
        $this->config = config('admin.settings');
    }

    /**
     * 테이블 이름 반환
     */
    protected function getTableName()
    {
        return 'admin_users';
    }

    /**
     * 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin_users';
    }

    /**
     * 관리자 목록 (추상 메서드 구현)
     */
    protected function _index(Request $request): View
    {
        $query = AdminUser::query();

        // 필터 파라미터 처리
        $filters = [];
        
        // 기본 검색 (이름/이메일)
        if ($request->filled('filter_search')) {
            $search = $request->input('filter_search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
            $filters['search'] = $search;
        }
        
        // 상태 필터
        if ($request->filled('filter_status')) {
            $status = $request->input('filter_status');
            if (!empty($status)) {
                $query->where('status', $status);
                $filters['status'] = $status;
            }
        }
        
        // 등급 필터
        if ($request->filled('filter_type')) {
            $type = $request->input('filter_type');
            if (!empty($type)) {
                $query->where('type', $type);
                $filters['type'] = $type;
            }
        }
        
        // 이메일 인증 필터
        if ($request->filled('filter_is_verified')) {
            $isVerified = $request->input('filter_is_verified');
            if (!empty($isVerified)) {
                $query->where('email_verified_at', $isVerified == '1' ? '!=' : '=', null);
                $filters['is_verified'] = $isVerified;
            }
        }
        
        // 등록일 필터
        if ($request->filled('filter_created_at')) {
            $createdAt = $request->input('filter_created_at');
            if (!empty($createdAt)) {
                switch ($createdAt) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereMonth('created_at', now()->month);
                        break;
                    case 'year':
                        $query->whereYear('created_at', now()->year);
                        break;
                }
                $filters['created_at'] = $createdAt;
            }
        }
        
        // 전화번호 필터
        if ($request->filled('filter_phone')) {
            $phone = $request->input('filter_phone');
            $query->where('phone', 'like', "%{$phone}%");
            $filters['phone'] = $phone;
        }
        
        // 로그인 횟수 필터
        if ($request->filled('filter_login_count')) {
            $loginCount = $request->input('filter_login_count');
            if (!empty($loginCount)) {
                $query->where('login_count', '>=', $loginCount);
                $filters['login_count'] = $loginCount;
            }
        }
        
        // 메모 필터
        if ($request->filled('filter_memo')) {
            $memo = $request->input('filter_memo');
            $query->where('memo', 'like', "%{$memo}%");
            $filters['memo'] = $memo;
        }

        // 정렬
        $query = $this->sort($query, $request);

        // 페이징
        $perPage = $request->get('per_page', 20);
        $users = $query->paginate($perPage)->appends($request->all());

        // 통계 데이터
        $stats = [
            'total' => AdminUser::count(),
            'active' => AdminUser::where('status', 'active')->count(),
            'inactive' => AdminUser::where('status', 'inactive')->count(),
            'suspended' => AdminUser::where('status', 'suspended')->count(),
        ];

        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('direction', 'desc');
        
        return view('jiny-admin::admin.users.index', [
            'rows' => $users,
            'stats' => $stats,
            'sort' => $sort,
            'dir' => $dir,
            'filters' => $filters
        ]);
    }

    /**
     * 관리자 생성 폼 (추상 메서드 구현)
     */
    protected function _create(Request $request): View
    {
        return view('jiny-admin::admin.users.create');
    }

    /**
     * 관리자 상세 조회 (추상 메서드 구현)
     */
    protected function _show(Request $request, $id): View
    {
        $user = AdminUser::findOrFail($id);
        
        // 2FA 상태 정보 추가
        $twoFactorInfo = [
            'has_2fa_enabled' => $user->has2FAEnabled(),
            'needs_2fa_setup' => $user->needs2FASetup(),
            'has_backup_codes' => $user->hasBackupCodes(),
            'google_2fa_enabled' => $user->hasGoogle2FAEnabled(),
            'ms_2fa_enabled' => $user->hasMS2FAEnabled(),
            'is_2fa_required' => $user->is2FARequired(),
            'is_2fa_setup_complete' => $user->is2FASetupComplete(),
            'is_2fa_verified' => $user->is2FAVerified(),
            '2fa_type' => $user->get2FAType(),
            '2fa_status' => $user->get2FAStatus(),
        ];
        
        return view('jiny-admin::admin.users.show', compact('user', 'twoFactorInfo'));
    }

    /**
     * 관리자 수정 폼 (추상 메서드 구현)
     */
    protected function _edit(Request $request, $id): View
    {
        $user = AdminUser::findOrFail($id);
        return view('jiny-admin::admin.users.edit', compact('user'));
    }

    /**
     * 관리자 저장 (추상 메서드 구현)
     */
    protected function _store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:admin_users,email',
                'password' => 'required|string|min:'.config('admin.settings.auth.password.min_length', 8),
                'type' => 'required|in:super,admin,staff',
                'status' => 'required|in:active,inactive,suspended',
            ], [
                'name.required' => '이름을 입력해주세요.',
                'name.string' => '이름은 문자열이어야 합니다.',
                'name.max' => '이름은 255자 이하여야 합니다.',
                'email.required' => '이메일을 입력해주세요.',
                'email.email' => '올바른 이메일 형식을 입력해주세요.',
                'email.unique' => '이미 사용 중인 이메일입니다.',
                'password.required' => '비밀번호를 입력해주세요.',
                'password.string' => '비밀번호는 문자열이어야 합니다.',
                'password.min' => '비밀번호는 최소 '.config('admin.settings.auth.password.min_length', 8).'자 이상이어야 합니다.',
                'type.required' => '등급을 선택해주세요.',
                'type.in' => '올바른 등급을 선택해주세요.',
                'status.required' => '상태를 선택해주세요.',
                'status.in' => '올바른 상태를 선택해주세요.',
            ]);

            // 패스워드 규칙 검사
            $passwordRules = config('admin.settings.auth.password') ?? [];
            $password = $validated['password'];
            $errors = [];
            
            if (isset($passwordRules['min_length']) && strlen($password) < $passwordRules['min_length']) {
                $errors['password'][] = '비밀번호는 최소 '.$passwordRules['min_length'].'자 이상이어야 합니다.';
            }
            if (isset($passwordRules['max_length']) && strlen($password) > $passwordRules['max_length']) {
                $errors['password'][] = '비밀번호는 최대 '.$passwordRules['max_length'].'자 이하여야 합니다.';
            }
            if (!empty($passwordRules['require_lowercase']) && !preg_match('/[a-z]/', $password)) {
                $errors['password'][] = '비밀번호에 소문자가 포함되어야 합니다.';
            }
            if (!empty($passwordRules['require_uppercase']) && !preg_match('/[A-Z]/', $password)) {
                $errors['password'][] = '비밀번호에 대문자가 포함되어야 합니다.';
            }
            if (!empty($passwordRules['require_numbers']) && !preg_match('/[0-9]/', $password)) {
                $errors['password'][] = '비밀번호에 숫자가 포함되어야 합니다.';
            }
            if (!empty($passwordRules['require_special_chars']) && !preg_match('/[\W_]/', $password)) {
                $errors['password'][] = '비밀번호에 특수문자가 포함되어야 합니다.';
            }
            
            if ($errors) {
                return response()->json([
                    'success' => false,
                    'errors' => $errors
                ], 422);
            }

            $validated['password'] = Hash::make($validated['password']);
            $user = AdminUser::create($validated);

            return response()->json([
                'success' => true,
                'message' => '관리자가 성공적으로 등록되었습니다.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '관리자 등록 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 관리자 수정 (추상 메서드 구현)
     */
    protected function _update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $user = AdminUser::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:admin_users,email,' . $user->id,
                'password' => 'nullable|string|min:'.config('admin.settings.auth.password.min_length', 8),
                'type' => 'required|in:super,admin,staff',
                'status' => 'required|in:active,inactive,suspended',
            ], [
                'name.required' => '이름을 입력해주세요.',
                'name.string' => '이름은 문자열이어야 합니다.',
                'name.max' => '이름은 255자 이하여야 합니다.',
                'email.required' => '이메일을 입력해주세요.',
                'email.email' => '올바른 이메일 형식을 입력해주세요.',
                'email.unique' => '이미 사용 중인 이메일입니다.',
                'password.string' => '비밀번호는 문자열이어야 합니다.',
                'password.min' => '비밀번호는 최소 '.config('admin.settings.auth.password.min_length', 8).'자 이상이어야 합니다.',
                'type.required' => '등급을 선택해주세요.',
                'type.in' => '올바른 등급을 선택해주세요.',
                'status.required' => '상태를 선택해주세요.',
                'status.in' => '올바른 상태를 선택해주세요.',
            ]);

            // 패스워드가 입력된 경우에만 규칙 검사
            if (!empty($validated['password'])) {
                $passwordRules = config('admin.settings.auth.password') ?? [];
                $password = $validated['password'];
                $errors = [];
                
                if (isset($passwordRules['min_length']) && strlen($password) < $passwordRules['min_length']) {
                    $errors['password'][] = '비밀번호는 최소 '.$passwordRules['min_length'].'자 이상이어야 합니다.';
                }
                if (isset($passwordRules['max_length']) && strlen($password) > $passwordRules['max_length']) {
                    $errors['password'][] = '비밀번호는 최대 '.$passwordRules['max_length'].'자 이하여야 합니다.';
                }
                if (!empty($passwordRules['require_lowercase']) && !preg_match('/[a-z]/', $password)) {
                    $errors['password'][] = '비밀번호에 소문자가 포함되어야 합니다.';
                }
                if (!empty($passwordRules['require_uppercase']) && !preg_match('/[A-Z]/', $password)) {
                    $errors['password'][] = '비밀번호에 대문자가 포함되어야 합니다.';
                }
                if (!empty($passwordRules['require_numbers']) && !preg_match('/[0-9]/', $password)) {
                    $errors['password'][] = '비밀번호에 숫자가 포함되어야 합니다.';
                }
                if (!empty($passwordRules['require_special_chars']) && !preg_match('/[\W_]/', $password)) {
                    $errors['password'][] = '비밀번호에 특수문자가 포함되어야 합니다.';
                }
                
                if ($errors) {
                    return response()->json([
                        'success' => false,
                        'errors' => $errors
                    ], 422);
                }

                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => '관리자 정보가 성공적으로 수정되었습니다.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '관리자 수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 관리자 삭제 (추상 메서드 구현)
     */
    protected function _destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $id = $request->get('id') ?? $request->route('id');
            $user = AdminUser::findOrFail($id);
            
            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = $user->toArray();
            
            $user->delete();

            // Activity Log 기록
            $this->logActivity('delete', '삭제', $oldData, ['deleted_id' => $id]);
            
            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '관리자 삭제', null);

            return response()->json([
                'success' => true,
                'message' => '관리자가 성공적으로 삭제되었습니다.',
                'data' => [
                    'id' => $id,
                    'name' => $oldData['name'] ?? 'Unknown'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 삭제 확인 폼 반환
     */
    public function deleteConfirm(Request $request, $id)
    {
        $user = AdminUser::findOrFail($id);
        $url = route('admin.admin.users.destroy', $id);
        $title = $user->name.' 삭제';
        
        // AJAX 요청인 경우 HTML만 반환
        if ($request->ajax()) {
            return view('jiny-admin::admin.users.form_delete', compact('user', 'url', 'title'));
        }
        
        // 일반 요청인 경우 전체 페이지 반환
        return view('jiny-admin::admin.users.form_delete', compact('user', 'url', 'title'));
    }

    /**
     * 수정 전 데이터 가져오기 (Audit Log용)
     */
    protected function getOldData($id)
    {
        $user = AdminUser::find($id);
        return $user ? $user->toArray() : null;
    }

    /**
     * 일괄 삭제
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:admin_users,id',
        ]);

        $ids = $request->ids;
        $count = count($ids);

        // 삭제 전 데이터 가져오기 (Audit Log용)
        $oldData = AdminUser::whereIn('id', $ids)->get()->toArray();

        AdminUser::whereIn('id', $ids)->delete();

        // Activity Log 기록
        $this->logActivity('delete', '일괄 삭제', null, ['deleted_ids' => $ids]);
        
        // Audit Log 기록
        $this->logAudit('delete', $oldData, null, '관리자 일괄 삭제', null);

        return response()->json([
            'success' => true,
            'message' => "{$count}명의 관리자가 성공적으로 삭제되었습니다.",
        ]);
    }
}
