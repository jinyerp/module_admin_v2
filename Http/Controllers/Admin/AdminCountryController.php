<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\AdminAuditLogTrait;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class AdminCountryController extends Controller
{
    use AdminAuditLogTrait;

    private $filterable = [];
    private $validFilters = [];

    /**
     * 로깅 활성화
     */
    protected $activeLog = true;

    /**
     * 로그 테이블명
     */
    protected $logTableName = 'countries';

    public function __construct()
    {
        $this->filterable = [
            'name', // name: 국가명 (문자열)
            'code', // code: 국가 코드 (2자리)
            'code3', // code3: 국가 코드 (3자리)
            'currency_code', // currency_code: 통화 코드 (3자리)
            'language_code', // language_code: 언어 코드 (2자리)
            'timezone', // timezone: 시간대 (문자열)
            'phone_code', // phone_code: 국가 전화번호 (숫자)
            'is_active', // is_active: 활성화 여부 (boolean)
            'is_default', // is_default: 기본 국가 여부 (boolean)
            'sort_order' // sort_order: 정렬 순서 (숫자)
        ];

        $this->validFilters = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:2',
            'code3' => 'required|string|size:3',
            'flag' => 'nullable|string|max:255',
            'currency_code' => 'nullable|string|size:3',
            'language_code' => 'nullable|string|max:5',
            'timezone' => 'nullable|string|max:255',
            'phone_code' => 'nullable|string|max:10',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer|min:0',
            'metadata' => 'nullable|json',
        ];
    }

    /**
     * 국가 목록 조회
     */
    public function index(Request $request): View
    {
        $query = Country::query();

        // 필터 파라미터 추출, 조건 적용용
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query);

        // 정렬
        $sortField = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $rows = $query->paginate(15);

        // 목록 출력
        return view('jiny.admin::admin.countries.index', [
            'countries' => $rows, // 데이터
            'filters' => $filters, // 필터
            'sort' => $sortField, // 정렬
            'dir' => $sortDirection, // 정렬 방향
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 필터링 적용
     * @param Request $request
     * @param Builder $query
     * @return Builder
     */
    public function applyFilter($filters, $query)
    {
        // 기본 필터 적용
        foreach ($this->filterable as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }

        // 검색어(부분일치) 별도 처리
        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%")
                  ->orWhere('code3', 'like', "%{$filters['search']}%");
            });
        }

        return $query;
    }

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
     * 국가 생성 폼
     */
    public function create(): View
    {
        return view('jiny.admin::admin.countries.create', [
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 국가 저장
     */
    public function store(Request $request): RedirectResponse
    {
        // 디버깅: 요청 데이터 로그
        \Log::info('Country Store Request', [
            'request_data' => $request->all(),
            'has_is_active' => $request->has('is_active'),
            'has_is_default' => $request->has('is_default'),
        ]);

        // 유효성 검사 규칙 (생성용)
        $validationRules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:2|unique:countries,code',
            'code3' => 'nullable|string|size:3|unique:countries,code3',
            'currency_code' => 'nullable|string|size:3',
            'language_code' => 'nullable|string|size:2',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Country Store Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        // 체크박스 처리
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['is_default'] = $request->has('is_default');

        // 디버깅: 처리된 데이터 로그
        \Log::info('Country Store Processed Data', [
            'processed_data' => $data,
        ]);

        // 기본 국가 설정 시 기존 기본 국가 해제
        if ($data['is_default']) {
            Country::where('is_default', true)->update(['is_default' => false]);
        }

        $country = Country::create($data);

        // 관리자 액션 로깅
        $this->logCreateAction($country, $data, "새로운 국가 생성: {$country->name}");

        // 디버깅: 생성된 데이터 로그
        \Log::info('Country Store Completed', [
            'created_country' => $country->toArray(),
        ]);

        return redirect()->route('admin.system.countries.index')
            ->with('success', '성공적으로 생성되었습니다.');
    }

    /**
     * 국가 상세 조회
     */
    public function show(Country $country): View
    {
        return view('jiny.admin::admin.countries.show', [
            'country' => $country,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 국가 수정 폼
     */
    public function edit(Country $country): View
    {
        return view('jiny.admin::admin.countries.edit', [
            'country' => $country,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 국가 업데이트
     */
    public function update(Request $request, Country $country): RedirectResponse
    {
        // 디버깅: 요청 데이터 로그
        \Log::info('Country Update Request', [
            'country_id' => $country->id,
            'request_data' => $request->all(),
            'has_is_active' => $request->has('is_active'),
            'has_is_default' => $request->has('is_default'),
        ]);

        // 유효성 검사 규칙 (수정용 - unique 규칙에서 현재 레코드 제외)
        $validationRules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:2|unique:countries,code,' . $country->id,
            'code3' => 'nullable|string|size:3|unique:countries,code3,' . $country->id,
            'currency_code' => 'nullable|string|size:3',
            'language_code' => 'nullable|string|size:2',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Country Update Validation Failed', [
                'country_id' => $country->id,
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        // 체크박스 처리
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['is_default'] = $request->has('is_default');

        // 디버깅: 처리된 데이터 로그
        \Log::info('Country Update Processed Data', [
            'processed_data' => $data,
        ]);

        // 업데이트 전 원본 데이터 저장
        $oldValues = $country->toArray();

        // 기본 국가 설정 시 기존 기본 국가 해제
        if ($data['is_default'] && !$country->is_default) {
            Country::where('is_default', true)->update(['is_default' => false]);
        }

        $country->update($data);

        // 관리자 액션 로깅
        $this->logUpdateAction($country, $oldValues, $data, "국가 정보 수정: {$country->name}");

        // 디버깅: 업데이트 후 데이터 로그
        \Log::info('Country Update Completed', [
            'updated_country' => $country->fresh()->toArray(),
        ]);

        return redirect()->route('admin.system.countries.index')
            ->with('success', '성공적으로 수정되었습니다.');
    }

    /**
     * 국가 삭제
     */
    public function destroy(Country $country)
    {
        try {
            // 기본 국가는 삭제 불가
            if ($country->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => '기본 국가는 삭제할 수 없습니다.'
                ], 400);
            }

            // 삭제 전 원본 데이터 저장
            $oldValues = $country->toArray();

            $country->delete();

            // 관리자 액션 로깅
            $this->logDeleteAction($country, $oldValues, "국가 삭제: {$country->name}");

            // 플래시 메시지 설정
            session()->flash('deleted', '성공적으로 삭제되었습니다.');

            return response()->json([
                'success' => true,
                'message' => '성공적으로 삭제되었습니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 국가 활성화/비활성화 토글
     */
    public function toggleActive(Country $country): RedirectResponse
    {
        $oldValues = $country->toArray();
        $country->update(['is_active' => !$country->is_active]);

        // 관리자 액션 로깅
        $action = $country->is_active ? '활성화' : '비활성화';
        $this->logUpdateAction($country, $oldValues, $country->toArray(), "국가 {$action}: {$country->name}");

        $status = $country->is_active ? '활성화' : '비활성화';
        return redirect()->route('admin.system.countries.index')
            ->with('success', "국가가 {$status}되었습니다.");
    }

    /**
     * 기본 국가 설정
     */
    public function setDefault(Country $country): RedirectResponse
    {
        DB::transaction(function() use ($country) {
            // 기존 기본 국가 해제
            Country::where('is_default', true)->update(['is_default' => false]);

            // 새로운 기본 국가 설정
            $country->update(['is_default' => true]);
        });

        // 관리자 액션 로깅
        $this->logUpdateAction($country, [], $country->fresh()->toArray(), "기본 국가 설정: {$country->name}");

        return redirect()->route('admin.system.countries.index')
            ->with('success', '기본 국가가 설정되었습니다.');
    }

    /**
     * 국가 순서 변경
     */
    public function updateOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'countries' => 'required|array',
            'countries.*' => 'integer|exists:countries,id',
        ]);

        foreach ($request->get('countries') as $index => $countryId) {
            Country::where('id', $countryId)->update(['sort_order' => $index + 1]);
        }

        return redirect()->route('admin.system.countries.index')
            ->with('success', '국가 순서가 업데이트되었습니다.');
    }

    /**
     * 국가 통계
     */
    public function stats(): View
    {
        $stats = [
            'total' => Country::count(),
            'active' => Country::where('is_active', true)->count(),
            'inactive' => Country::where('is_active', false)->count(),
            'default' => Country::where('is_default', true)->count(),
        ];

        return view('jiny.admin::admin.countries.stats', [
            'stats' => $stats,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 선택 삭제 (bulk delete)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        // 입력된 값이 배열인지 확인합니다.
        if (!is_array($request->input('ids'))) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 입력입니다.'
            ], 422);
        }

        // ids 배열을 정수로 변환
        $ids = array_map('intval', $request->input('ids'));

        // 삭제할 국가 정보 조회 (로깅용)
        $countriesToDelete = Country::whereIn('id', $ids)->get();

        // 데이터를 삭제합니다.
        $deletedCount = Country::whereIn('id', $ids)->delete();

        // 관리자 액션 로깅
        $this->logBulkDeleteAction($ids, $deletedCount, "대량 국가 삭제: {$deletedCount}개");

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount}개의 국가가 성공적으로 삭제되었습니다."
        ]);
    }
}
