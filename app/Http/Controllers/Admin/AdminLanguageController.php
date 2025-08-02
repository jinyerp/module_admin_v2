<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Jiny\Admin\App\Models\Language;
use Jiny\Admin\App\Http\Controllers\AdminResourceController;

class AdminLanguageController extends AdminResourceController
{
    protected $sortableColumns = ['id', 'name', 'code', 'flag', 'country', 'users', 'users_percent', 'enable', 'sort_order', 'created_at', 'updated_at'];
    protected $filterable = ['name', 'code', 'flag', 'country', 'users', 'users_percent', 'enable', 'sort_order'];
    private $config;

    public function __construct()
    {
        // 패키지의 admin config 읽어오기
        $this->config = config('admin.settings');
    }

    /**
     * 언어 목록 (템플릿 메소드 구현)
     */
    public function _index(Request $request): View
    {
        $query = Language::query();

        // 필터링
        if ($request->filled('filter_name')) {
            $query->where('name', 'like', '%' . $request->filter_name . '%');
        }
        if ($request->filled('filter_code')) {
            $query->where('code', 'like', '%' . $request->filter_code . '%');
        }
        if ($request->filled('filter_flag')) {
            $query->where('flag', 'like', '%' . $request->filter_flag . '%');
        }
        if ($request->filled('filter_country')) {
            $query->where('country', 'like', '%' . $request->filter_country . '%');
        }
        if ($request->filled('filter_users')) {
            $query->where('users', 'like', '%' . $request->filter_users . '%');
        }
        if ($request->filled('filter_users_percent')) {
            $query->where('users_percent', 'like', '%' . $request->filter_users_percent . '%');
        }
        if ($request->filled('filter_enable')) {
            $query->where('enable', $request->filter_enable);
        }
        if ($request->filled('filter_sort_order')) {
            $query->where('sort_order', $request->filter_sort_order);
        }

        // 정렬
        $sortBy = $request->get('sort', 'sort_order');
        $sortOrder = $request->get('order', 'asc');
        
        if (in_array($sortBy, $this->sortableColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('sort_order', 'asc');
        }

        // 페이지네이션
        $perPage = $request->get('per_page', 15);
        $rows = $query->paginate($perPage);

        // 필터 데이터 전달
        $filters = $request->only([
            'filter_name', 'filter_code', 'filter_flag', 'filter_country',
            'filter_users', 'filter_users_percent', 'filter_enable', 'filter_sort_order'
        ]);

        return view('jiny-admin::admin.language.index', [
            'rows' => $rows,
            'filters' => $filters,
            'route' => 'admin.language.',
        ]);
    }

    /**
     * 언어 생성 폼
     */
    public function _create(Request $request): View
    {
        return view('jiny-admin::admin.language.create');
    }

    /**
     * 언어 상세 보기
     */
    public function _show(Request $request, $id): View
    {
        $language = Language::findOrFail($id);
        return view('jiny-admin::admin.language.show', compact('language'));
    }

    /**
     * 언어 수정 폼
     */
    public function _edit(Request $request, $id): View
    {
        $language = Language::findOrFail($id);
        return view('jiny-admin::admin.language.edit', compact('language'));
    }

    /**
     * 언어 저장
     */
    public function _store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10|unique:admin_language,code',
                'flag' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'users' => 'nullable|string|max:255',
                'users_percent' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer|min:0',
                'enable' => 'boolean',
            ], [
                'name.required' => '언어명을 입력해주세요.',
                'name.max' => '언어명은 255자를 초과할 수 없습니다.',
                'code.required' => '언어코드를 입력해주세요.',
                'code.max' => '언어코드는 10자를 초과할 수 없습니다.',
                'code.unique' => '이미 존재하는 언어코드입니다.',
                'flag.max' => '국기 정보는 255자를 초과할 수 없습니다.',
                'country.max' => '국가 정보는 255자를 초과할 수 없습니다.',
                'users.max' => '사용자 정보는 255자를 초과할 수 없습니다.',
                'users_percent.max' => '사용자 비율 정보는 255자를 초과할 수 없습니다.',
                'sort_order.integer' => '정렬순서는 숫자여야 합니다.',
                'sort_order.min' => '정렬순서는 0 이상이어야 합니다.',
            ]);

            $validated['enable'] = $request->has('enable');

            $language = Language::create($validated);

            return response()->json([
                'success' => true,
                'message' => '언어가 성공적으로 등록되었습니다.',
                'data' => [
                    'id' => $language->id,
                    'name' => $language->name,
                    'code' => $language->code
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
                'message' => '언어 등록 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 언어 수정
     */
    public function _update(Request $request, $id): JsonResponse
    {
        try {
            $language = Language::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10|unique:admin_language,code,' . $id,
                'flag' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'users' => 'nullable|string|max:255',
                'users_percent' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer|min:0',
                'enable' => 'boolean',
            ], [
                'name.required' => '언어명을 입력해주세요.',
                'name.max' => '언어명은 255자를 초과할 수 없습니다.',
                'code.required' => '언어코드를 입력해주세요.',
                'code.max' => '언어코드는 10자를 초과할 수 없습니다.',
                'code.unique' => '이미 존재하는 언어코드입니다.',
                'flag.max' => '국기 정보는 255자를 초과할 수 없습니다.',
                'country.max' => '국가 정보는 255자를 초과할 수 없습니다.',
                'users.max' => '사용자 정보는 255자를 초과할 수 없습니다.',
                'users_percent.max' => '사용자 비율 정보는 255자를 초과할 수 없습니다.',
                'sort_order.integer' => '정렬순서는 숫자여야 합니다.',
                'sort_order.min' => '정렬순서는 0 이상이어야 합니다.',
            ]);

            $validated['enable'] = $request->has('enable');

            $language->update($validated);

            return response()->json([
                'success' => true,
                'message' => '언어가 성공적으로 수정되었습니다.',
                'data' => [
                    'id' => $language->id,
                    'name' => $language->name,
                    'code' => $language->code
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '언어 수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 언어 삭제
     */
    public function _destroy(Request $request): JsonResponse
    {
        try {
            $id = $request->input('id');
            $language = Language::findOrFail($id);

            $language->delete();

            return response()->json([
                'success' => true,
                'message' => '언어가 성공적으로 삭제되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '언어 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 삭제 확인 폼
     */
    public function deleteConfirm(Request $request, $id)
    {
        $language = Language::findOrFail($id);
        $randomKey = strtoupper(substr(md5(uniqid()), 0, 8));
        
        return view('jiny-admin::admin.language.form_delete', [
            'language' => $language,
            'title' => '언어 삭제',
            'randomKey' => $randomKey
        ]);
    }

    /**
     * 일괄 삭제
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:admin_language,id'
            ]);

            $ids = $request->input('ids');

            Language::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($ids) . '개의 언어가 성공적으로 삭제되었습니다.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '일괄 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 활성화 토글
     */
    public function toggleActive(Language $language): RedirectResponse
    {
        $language->update(['enable' => !$language->enable]);
        
        return redirect()->route('admin.language.index')
            ->with('success', '언어 상태가 변경되었습니다.');
    }

    /**
     * 정렬 순서 업데이트
     */
    public function updateOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer|exists:admin_language,id'
        ]);

        $orders = $request->input('orders');
        
        foreach ($orders as $index => $id) {
            Language::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return redirect()->route('admin.language.index')
            ->with('success', '정렬 순서가 업데이트되었습니다.');
    }

    /**
     * 통계 정보
     */
    public function stats(): View
    {
        $stats = [
            'total' => Language::count(),
            'enabled' => Language::where('enable', true)->count(),
            'disabled' => Language::where('enable', false)->count(),
        ];

        return view('jiny-admin::admin.language.stats', compact('stats'));
    }

    /**
     * AJAX 활성화 토글
     */
    public function toggleEnableAjax(Request $request, $id): JsonResponse
    {
        try {
            $language = Language::findOrFail($id);
            $language->update(['enable' => !$language->enable]);

            return response()->json([
                'success' => true,
                'message' => '상태가 변경되었습니다.',
                'enable' => $language->enable
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '상태 변경 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX 전체 활성화
     */
    public function enableAllAjax(Request $request): JsonResponse
    {
        try {
            Language::query()->update(['enable' => true]);

            return response()->json([
                'success' => true,
                'message' => '모든 언어가 활성화되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '전체 활성화 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 기존 데이터 조회
     */
    protected function getOldData($id)
    {
        return Language::find($id);
    }

    /**
     * 테이블명 반환
     */
    protected function getTableName()
    {
        return 'admin_language';
    }

    /**
     * 모듈명 반환
     */
    protected function getModuleName()
    {
        return 'language';
    }

    /**
     * 기본 언어 설정
     */
    public function setDefault(Request $request)
    {
        try {
            \Log::info('기본 언어 설정 요청 시작', [
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            $languageCode = $request->input('code');
            
            if (!$languageCode) {
                \Log::error('언어 코드가 제공되지 않음');
                return response()->json([
                    'success' => false,
                    'message' => '언어 코드가 제공되지 않았습니다.'
                ], 400);
            }

            \Log::info('언어 코드 확인', ['code' => $languageCode]);

            // 언어가 존재하는지 확인
            $language = Language::where('code', $languageCode)->first();
            if (!$language) {
                \Log::error('언어를 찾을 수 없음', ['code' => $languageCode]);
                return response()->json([
                    'success' => false,
                    'message' => "언어 코드 '{$languageCode}'를 찾을 수 없습니다."
                ], 404);
            }

            \Log::info('언어 확인 완료', ['language' => $language->toArray()]);

            // 데이터베이스 트랜잭션 시작
            DB::beginTransaction();

            try {
                // 기존 기본 언어 해제
                Language::where('is_default', true)->update(['is_default' => false]);
                
                // 새로운 기본 언어 설정
                $language->update(['is_default' => true]);

                DB::commit();
                
                \Log::info('기본 언어 설정 성공', ['code' => $languageCode]);
                
                return response()->json([
                    'success' => true,
                    'message' => '기본 언어가 설정되었습니다.'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('기본 언어 설정 중 예외 발생', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '기본 언어 설정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 현재 기본 언어 정보 조회
     */
    public function syncLocale(Request $request)
    {
        try {
            \Log::info('기본 언어 정보 조회 요청');

            // 데이터베이스에서 기본 언어 조회
            $defaultLanguage = Language::where('is_default', true)->first();
            
            if (!$defaultLanguage) {
                return response()->json([
                    'success' => false,
                    'message' => '기본 언어가 설정되지 않았습니다.'
                ], 404);
            }

            \Log::info('기본 언어 정보 조회 완료', [
                'code' => $defaultLanguage->code,
                'name' => $defaultLanguage->name
            ]);

            return response()->json([
                'success' => true,
                'message' => '기본 언어 정보를 조회했습니다.',
                'data' => [
                    'code' => $defaultLanguage->code,
                    'name' => $defaultLanguage->name
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('기본 언어 정보 조회 중 오류', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => '기본 언어 정보 조회 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}
