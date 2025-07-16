<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;

class AdminCurrencyController extends Controller
{
    /**
     * 통화 목록 조회
     */
    public function index(Request $request): View
    {
        $query = Currency::query();

        // 검색 필터
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
            });
        }

        // 활성화 상태 필터
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->get('is_active'));
        }

        // 기본 통화 필터
        if ($request->filled('is_default')) {
            $query->where('is_default', $request->get('is_default'));
        }

        // 정렬
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $currencies = $query->paginate(20);

        return view('jiny-admin::admin.currencies.index', [
            'currencies' => $currencies,
            'sort' => $sortField,
            'dir' => $sortDirection,
        ]);
    }

    /**
     * 통화 생성 폼
     */
    public function create(): View
    {
        return view('jiny-admin::admin.currencies.create');
    }

    /**
     * 통화 저장
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:3|unique:currencies,code',
            'symbol' => 'required|string|max:10',
            'decimal_places' => 'required|integer|min:0|max:4',
            'exchange_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer|min:0',
            'metadata' => 'nullable|json',
        ]);

        // 기본 통화 설정 시 기존 기본 통화 해제
        if ($request->boolean('is_default')) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        Currency::create($request->all());

        return redirect()->route('admin.currencies.index')
            ->with('success', '통화가 성공적으로 생성되었습니다.');
    }

    /**
     * 통화 상세 조회
     */
    public function show(Currency $currency): View
    {
        return view('jiny-admin::admin.currencies.show', compact('currency'));
    }

    /**
     * 통화 수정 폼
     */
    public function edit(Currency $currency): View
    {
        return view('jiny-admin::admin.currencies.edit', compact('currency'));
    }

    /**
     * 통화 업데이트
     */
    public function update(Request $request, Currency $currency): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:3|unique:currencies,code,' . $currency->id,
            'symbol' => 'required|string|max:10',
            'decimal_places' => 'required|integer|min:0|max:4',
            'exchange_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer|min:0',
            'metadata' => 'nullable|json',
        ]);

        // 기본 통화 설정 시 기존 기본 통화 해제
        if ($request->boolean('is_default') && !$currency->is_default) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency->update($request->all());

        return redirect()->route('admin.currencies.index')
            ->with('success', '통화가 성공적으로 수정되었습니다.');
    }

    /**
     * 통화 삭제
     */
    public function destroy(Currency $currency): RedirectResponse
    {
        // 기본 통화는 삭제 불가
        if ($currency->is_default) {
            return redirect()->route('admin.currencies.index')
                ->with('error', '기본 통화는 삭제할 수 없습니다.');
        }

        $currency->delete();

        return redirect()->route('admin.currencies.index')
            ->with('success', '통화가 성공적으로 삭제되었습니다.');
    }

    /**
     * 통화 활성화/비활성화 토글
     */
    public function toggleActive(Currency $currency): RedirectResponse
    {
        $currency->update(['is_active' => !$currency->is_active]);

        $status = $currency->is_active ? '활성화' : '비활성화';
        return redirect()->route('admin.currencies.index')
            ->with('success', "통화가 {$status}되었습니다.");
    }

    /**
     * 기본 통화 설정
     */
    public function setDefault(Currency $currency): RedirectResponse
    {
        DB::transaction(function() use ($currency) {
            // 기존 기본 통화 해제
            Currency::where('is_default', true)->update(['is_default' => false]);

            // 새로운 기본 통화 설정
            $currency->update(['is_default' => true]);
        });

        return redirect()->route('admin.currencies.index')
            ->with('success', '기본 통화가 설정되었습니다.');
    }

    /**
     * 통화 순서 변경
     */
    public function updateOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'currencies' => 'required|array',
            'currencies.*' => 'integer|exists:currencies,id',
        ]);

        foreach ($request->get('currencies') as $index => $currencyId) {
            Currency::where('id', $currencyId)->update(['sort_order' => $index + 1]);
        }

        return redirect()->route('admin.currencies.index')
            ->with('success', '통화 순서가 업데이트되었습니다.');
    }

    /**
     * 환율 업데이트
     */
    public function updateExchangeRate(Request $request, Currency $currency): RedirectResponse
    {
        $request->validate([
            'exchange_rate' => 'required|numeric|min:0',
        ]);

        $currency->update(['exchange_rate' => $request->exchange_rate]);

        return redirect()->route('admin.currencies.index')
            ->with('success', '환율이 업데이트되었습니다.');
    }

    /**
     * 통화 통계
     */
    public function stats(): View
    {
        $stats = [
            'total' => Currency::count(),
            'active' => Currency::where('is_active', true)->count(),
            'inactive' => Currency::where('is_active', false)->count(),
            'default' => Currency::where('is_default', true)->count(),
        ];

        $recentCurrencies = Currency::orderBy('created_at', 'desc')->limit(5)->get();

        return view('jiny-admin::admin.currencies.stats', compact('stats', 'recentCurrencies'));
    }

    /**
     * 통화 일괄 삭제
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'currencies' => 'required|array',
            'currencies.*' => 'integer|exists:currencies,id',
        ]);

        $currencyIds = $request->get('currencies');
        $currencies = Currency::whereIn('id', $currencyIds)->get();

        $deletedCount = 0;
        $errorCount = 0;

        foreach ($currencies as $currency) {
            // 기본 통화는 삭제 불가
            if ($currency->is_default) {
                $errorCount++;
                continue;
            }

            $currency->delete();
            $deletedCount++;
        }

        $message = "{$deletedCount}개의 통화가 삭제되었습니다.";
        if ($errorCount > 0) {
            $message .= " ({$errorCount}개의 기본 통화는 삭제할 수 없습니다.)";
        }

        return redirect()->route('admin.currencies.index')
            ->with('success', $message);
    }
}
