<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Language;
use Illuminate\Support\Facades\DB;

class AdminLanguageController extends Controller
{
    /**
     * 언어 목록 조회
     */
    public function index(Request $request): View
    {
        $query = Language::query();

        // 검색 필터
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('native_name', 'like', "%{$search}%");
            });
        }

        // 활성화 상태 필터
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->get('is_active'));
        }

        // 기본 언어 필터
        if ($request->filled('is_default')) {
            $query->where('is_default', $request->get('is_default'));
        }

        // 정렬
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $languages = $query->paginate(20);

        return view('jiny-admin::admin.languages.index', [
            'languages' => $languages,
            'sort' => $sortField,
            'dir' => $sortDirection,
        ]);
    }

    /**
     * 언어 생성 폼
     */
    public function create(): View
    {
        return view('jiny-admin::admin.languages.create');
    }

    /**
     * 언어 저장
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:5|unique:languages,code',
            'native_name' => 'nullable|string|max:255',
            'flag' => 'nullable|string|max:255',
            'direction' => 'required|in:ltr,rtl',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer|min:0',
            'metadata' => 'nullable|json',
        ]);

        // 기본 언어 설정 시 기존 기본 언어 해제
        if ($request->boolean('is_default')) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        Language::create($request->all());

        return redirect()->route('admin.languages.index')
            ->with('success', '언어가 성공적으로 생성되었습니다.');
    }

    /**
     * 언어 상세 조회
     */
    public function show(Language $language): View
    {
        return view('jiny-admin::admin.languages.show', compact('language'));
    }

    /**
     * 언어 수정 폼
     */
    public function edit(Language $language): View
    {
        return view('jiny-admin::admin.languages.edit', compact('language'));
    }

    /**
     * 언어 업데이트
     */
    public function update(Request $request, Language $language): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:5|unique:languages,code,' . $language->id,
            'native_name' => 'nullable|string|max:255',
            'flag' => 'nullable|string|max:255',
            'direction' => 'required|in:ltr,rtl',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer|min:0',
            'metadata' => 'nullable|json',
        ]);

        // 기본 언어 설정 시 기존 기본 언어 해제
        if ($request->boolean('is_default') && !$language->is_default) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        $language->update($request->all());

        return redirect()->route('admin.languages.index')
            ->with('success', '언어가 성공적으로 수정되었습니다.');
    }

    /**
     * 언어 삭제
     */
    public function destroy(Language $language)
    {
        try {
            // 기본 언어는 삭제 불가
            if ($language->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => '기본 언어는 삭제할 수 없습니다.'
                ], 400);
            }

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
     * 언어 활성화/비활성화 토글
     */
    public function toggleActive(Language $language): RedirectResponse
    {
        $language->update(['is_active' => !$language->is_active]);

        $status = $language->is_active ? '활성화' : '비활성화';
        return redirect()->route('admin.languages.index')
            ->with('success', "언어가 {$status}되었습니다.");
    }

    /**
     * 기본 언어 설정
     */
    public function setDefault(Language $language): RedirectResponse
    {
        DB::transaction(function() use ($language) {
            // 기존 기본 언어 해제
            Language::where('is_default', true)->update(['is_default' => false]);

            // 새로운 기본 언어 설정
            $language->update(['is_default' => true]);
        });

        return redirect()->route('admin.languages.index')
            ->with('success', '기본 언어가 설정되었습니다.');
    }

    /**
     * 언어 순서 변경
     */
    public function updateOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'languages' => 'required|array',
            'languages.*' => 'integer|exists:languages,id',
        ]);

        foreach ($request->get('languages') as $index => $languageId) {
            Language::where('id', $languageId)->update(['sort_order' => $index + 1]);
        }

        return redirect()->route('admin.languages.index')
            ->with('success', '언어 순서가 업데이트되었습니다.');
    }

    /**
     * 언어 통계
     */
    public function stats(): View
    {
        $stats = [
            'total' => Language::count(),
            'active' => Language::where('is_active', true)->count(),
            'inactive' => Language::where('is_active', false)->count(),
            'default' => Language::where('is_default', true)->count(),
            'ltr' => Language::where('direction', 'ltr')->count(),
            'rtl' => Language::where('direction', 'rtl')->count(),
        ];

        $recentLanguages = Language::orderBy('created_at', 'desc')->limit(5)->get();

        return view('jiny-admin::admin.languages.stats', compact('stats', 'recentLanguages'));
    }

    /**
     * 언어 일괄 삭제
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'languages' => 'required|array',
            'languages.*' => 'integer|exists:languages,id',
        ]);

        $languageIds = $request->get('languages');
        $languages = Language::whereIn('id', $languageIds)->get();

        $deletedCount = 0;
        $errorCount = 0;

        foreach ($languages as $language) {
            // 기본 언어는 삭제 불가
            if ($language->is_default) {
                $errorCount++;
                continue;
            }

            $language->delete();
            $deletedCount++;
        }

        $message = "{$deletedCount}개의 언어가 삭제되었습니다.";
        if ($errorCount > 0) {
            $message .= " ({$errorCount}개의 기본 언어는 삭제할 수 없습니다.)";
        }

        return redirect()->route('admin.languages.index')
            ->with('success', $message);
    }
}
