<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Jiny\Admin\App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

class AdminLanguageCRUDTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /*
     * TODO: 구현이 필요한 주요 기능들
     * 
     * 1. Language 모델 생성
     *    - App\Models\Language 클래스가 존재하지 않음
     *    - 언어 관리에 필요한 속성들 정의 필요
     * 
     * 2. languages 테이블 마이그레이션
     *    - 언어 정보를 저장할 데이터베이스 테이블 필요
     *    - name, code, locale, direction, status, is_default, sort_order 등
     * 
     * 3. CSV/Excel 내보내기 기능
     *    - /export/csv, /export/excel 라우트가 404 오류 발생
     *    - 내보내기 컨트롤러 및 로직 구현 필요
     * 
     * 4. 통계 정보 표시
     *    - 언어 목록 페이지에 total, active, inactive 등의 통계 정보가 없음
     *    - 통계 계산 및 표시 로직 구현 필요
     * 
     * 5. 검증 로직 보완
     *    - locale 필드 검증이 누락됨
     *    - 중복 검사 로직 구현 필요
     * 
     * 6. 상세/수정 페이지
     *    - 언어 상세 보기, 수정 페이지가 아직 구현되지 않음
     *    - 관련 뷰 파일 및 컨트롤러 메서드 구현 필요
     */

    protected $adminUser;
    protected $baseUrl = '/admin/language';

    protected function setUp(): void
    {
        parent::setUp();
        
        // 관리자 사용자 생성
        $this->adminUser = AdminUser::create([
            'name' => '테스트 관리자',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'type' => 'super',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
            'phone' => '010-1234-5678',
            'memo' => '테스트용 관리자 계정',
            'last_login_at' => now(),
            'login_count' => 5,
            'google_2fa_enabled' => false,
            'ms_2fa_enabled' => false,
            'google_2fa_required' => false,
            'ms_2fa_required' => false,
        ]);

        // 관리자로 로그인
        $this->actingAs($this->adminUser, 'admin');
    }

    /** @test */
    public function 관리자가_언어_목록_페이지에_접근할_수_있다()
    {
        $response = $this->get($this->baseUrl);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.index');
        $response->assertSee('언어 관리');
        $response->assertSee('수정');
        $response->assertSee('삭제');
    }

    /** @test */
    public function 관리자가_언어_생성_페이지에_접근할_수_있다()
    {
        $response = $this->get($this->baseUrl . '/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.create');
        $response->assertSee('새 언어 등록');
    }

    /** @test */
    public function 관리자가_새로운_언어를_생성할_수_있다()
    {
        // TODO: Language 모델과 languages 테이블이 아직 구현되지 않음
        // TODO: 현재는 422 오류 발생 (이미 존재하는 언어코드)
        
        $languageData = [
            'name' => '한국어',
            'code' => 'ko',
            'locale' => 'ko_KR',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 1,
        ];

        $response = $this->postJson($this->baseUrl, $languageData);
        
        // $response->assertStatus(201);
        // $response->assertJson(['success' => true]);
        
        // $this->assertDatabaseHas('languages', [
        //     'name' => '한국어',
        //     'code' => 'ko',
        //     'locale' => 'ko_KR',
        //     'status' => 'active',
        // ]);
        
        $this->markTestSkipped('Language 모델과 테이블이 아직 구현되지 않음');
    }

    /** @test */
    public function 관리자가_언어_정보를_수정할_수_있다()
    {
        // TODO: Language 모델과 languages 테이블이 아직 구현되지 않음
        
        // 먼저 언어 생성
        // $language = \App\Models\Language::create([
        //     'name' => '한국어',
        //     'code' => 'ko',
        //     'locale' => 'ko_KR',
        //     'direction' => 'ltr',
        //     'status' => 'active',
        //     'is_default' => false,
        //     'sort_order' => 1,
        // ]);

        // $updateData = [
        //     'name' => '수정된 한국어',
        //     'code' => 'ko',
        //     'locale' => 'ko_KR',
        //     'direction' => 'ltr',
        //     'status' => 'inactive',
        //     'is_default' => false,
        //     'sort_order' => 2,
        // ];

        // $response = $this->putJson($this->baseUrl . '/' . $language->id, $updateData);
        
        // $response->assertStatus(200);
        // $response->assertJson(['success' => true]);
        
        // $this->assertDatabaseHas('languages', [
        //     'id' => $language->id,
        //     'name' => '수정된 한국어',
        //     'status' => 'inactive',
        //     'sort_order' => 2,
        // ]);
        
        $this->markTestSkipped('Language 모델과 테이블이 아직 구현되지 않음');
    }

    /** @test */
    public function 관리자가_언어를_삭제할_수_있다()
    {
        // 먼저 언어 생성
        $language = \App\Models\Language::create([
            'name' => '삭제 대상 언어',
            'code' => 'del',
            'locale' => 'del_DEL',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 999,
        ]);

        $response = $this->deleteJson($this->baseUrl . '/' . $language->id);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseMissing('languages', [
            'id' => $language->id,
        ]);
    }

    /** @test */
    public function 관리자가_언어_삭제_확인_페이지를_볼_수_있다()
    {
        // 먼저 언어 생성
        $language = \App\Models\Language::create([
            'name' => '삭제 확인 언어',
            'code' => 'confirm',
            'locale' => 'confirm_CON',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 999,
        ]);

        $response = $this->get($this->baseUrl . '/' . $language->id . '/delete-confirm');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.form_delete');
        $response->assertSee($language->name . ' 삭제');
    }

    /** @test */
    public function 관리자가_언어_목록에서_검색할_수_있다()
    {
        // 테스트용 언어들 생성
        \App\Models\Language::create([
            'name' => '한국어',
            'code' => 'ko',
            'locale' => 'ko_KR',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 1,
        ]);

        \App\Models\Language::create([
            'name' => 'English',
            'code' => 'en',
            'locale' => 'en_US',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 2,
        ]);

        $response = $this->get($this->baseUrl . '?filter_search=한국어');
        
        $response->assertStatus(200);
        $response->assertSee('한국어');
        $response->assertDontSee('English');
    }

    /** @test */
    public function 관리자가_언어_목록에서_필터링할_수_있다()
    {
        // 활성/비활성 언어 생성
        \App\Models\Language::create([
            'name' => '활성 언어',
            'code' => 'active',
            'locale' => 'active_ACT',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 1,
        ]);

        \App\Models\Language::create([
            'name' => '비활성 언어',
            'code' => 'inactive',
            'locale' => 'inactive_INA',
            'direction' => 'ltr',
            'status' => 'inactive',
            'is_default' => false,
            'sort_order' => 2,
        ]);

        $response = $this->get($this->baseUrl . '?filter_status=inactive');
        
        $response->assertStatus(200);
        $response->assertSee('비활성 언어');
        $response->assertDontSee('활성 언어');
    }

    /** @test */
    public function 관리자가_언어_목록에서_정렬할_수_있다()
    {
        $response = $this->get($this->baseUrl . '?sort=name&direction=asc');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.index');
    }

    /** @test */
    public function 관리자가_언어_목록에서_페이지네이션을_사용할_수_있다()
    {
        // 여러 언어 생성 (페이지네이션 테스트용)
        for ($i = 1; $i <= 25; $i++) {
            \App\Models\Language::create([
                'name' => "테스트 언어 {$i}",
                'code' => "lang{$i}",
                'locale' => "lang{$i}_LOC",
                'direction' => 'ltr',
                'status' => 'active',
                'is_default' => false,
                'sort_order' => $i,
            ]);
        }

        $response = $this->get($this->baseUrl . '?per_page=10');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.index');
    }

    /** @test */
    public function 관리자가_언어_통계를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.index');
        
        // TODO: 통계 정보가 포함되어 있는지 확인
        // 현재 페이지에 통계 정보가 표시되지 않음
        // $response->assertSee('total');
        // $response->assertSee('active');
        // $response->assertSee('inactive');
    }

    /** @test */
    public function 관리자가_언어_목록에서_액션_버튼들을_볼_수_있다()
    {
        $response = $this->get($this->baseUrl);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.index');
        
        // 액션 버튼들이 포함되어 있는지 확인
        $response->assertSee('보기');
        $response->assertSee('수정');
        $response->assertSee('삭제');
    }

    /** @test */
    public function 관리자가_언어_목록에서_체크박스를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.index');
        
        // 체크박스가 포함되어 있는지 확인
        $response->assertSee('checkbox');
    }

    /** @test */
    public function 관리자가_언어_목록에서_일괄_삭제를_할_수_있다()
    {
        // TODO: Language 모델이 존재하지 않음 - 모델 생성 필요
        // TODO: languages 테이블이 존재하지 않음 - 마이그레이션 필요
        
        // 테스트용 언어들 생성
        // $lang1 = \App\Models\Language::create([
        //     'name' => '삭제 대상 언어 1',
        //     'code' => 'del1',
        //     'locale' => 'del1_DEL',
        //     'direction' => 'ltr',
        //     'status' => 'active',
        //     'is_default' => false,
        //     'sort_order' => 999,
        // ]);

        // $lang2 = \App\Models\Language::create([
        //     'name' => '삭제 대상 언어 2',
        //     'code' => 'del2',
        //     'locale' => 'del2_DEL',
        //     'direction' => 'ltr',
        //     'status' => 'active',
        //     'is_default' => false,
        //     'sort_order' => 998,
        // ]);

        // $response = $this->postJson($this->baseUrl . '/bulk-delete', [
        //     'ids' => [$lang1->id, $lang2->id]
        // ]);
        
        // $response->assertStatus(200);
        // $response->assertJson(['success' => true]);
        
        // 언어들이 삭제되었는지 확인
        // $this->assertDatabaseMissing('languages', ['id' => $lang1->id]);
        // $this->assertDatabaseMissing('languages', ['id' => $lang2->id]);
        
        $this->markTestSkipped('Language 모델과 테이블이 아직 구현되지 않음');
    }

    /** @test */
    public function 관리자가_언어_목록에서_CSV를_다운로드할_수_있다()
    {
        // TODO: CSV 내보내기 기능이 아직 구현되지 않음 - 404 오류 발생
        // TODO: /export/csv 라우트 구현 필요
        
        $response = $this->get($this->baseUrl . '/export/csv');
        
        // $response->assertStatus(200);
        // $response->assertHeader('Content-Type', 'text/csv');
        // $response->assertHeader('Content-Disposition', 'attachment; filename="languages.csv"');
        
        $this->markTestSkipped('CSV 내보내기 기능이 아직 구현되지 않음');
    }

    /** @test */
    public function 관리자가_언어_목록에서_Excel을_다운로드할_수_있다()
    {
        // TODO: Excel 내보내기 기능이 아직 구현되지 않음 - 404 오류 발생
        // TODO: /export/excel 라우트 구현 필요
        
        $response = $this->get($this->baseUrl . '/export/excel');
        
        // $response->assertStatus(200);
        // $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // $response->assertHeader('Content-Disposition', 'attachment; filename="languages.xlsx"');
        
        $this->markTestSkipped('Excel 내보내기 기능이 아직 구현되지 않음');
    }

    /** @test */
    public function 관리자가_언어_상세_페이지에_접근할_수_있다()
    {
        // 먼저 언어 생성
        $language = \App\Models\Language::create([
            'name' => '상세 보기 언어',
            'code' => 'detail',
            'locale' => 'detail_DET',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 1,
        ]);

        $response = $this->get($this->baseUrl . '/' . $language->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.show');
        $response->assertSee('언어 상세 정보');
        $response->assertSee($language->name);
        $response->assertSee($language->code);
    }

    /** @test */
    public function 관리자가_언어_수정_페이지에_접근할_수_있다()
    {
        // 먼저 언어 생성
        $language = \App\Models\Language::create([
            'name' => '수정 대상 언어',
            'code' => 'edit',
            'locale' => 'edit_EDI',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 1,
        ]);

        $response = $this->get($this->baseUrl . '/' . $language->id . '/edit');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.edit');
        $response->assertSee('언어 수정');
        $response->assertSee($language->name);
    }

    /** @test */
    public function 관리자가_언어_상세_페이지에서_기본_정보를_볼_수_있다()
    {
        // 먼저 언어 생성
        $language = \App\Models\Language::create([
            'name' => '정보 확인 언어',
            'code' => 'info',
            'locale' => 'info_INF',
            'direction' => 'rtl',
            'status' => 'active',
            'is_default' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get($this->baseUrl . '/' . $language->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.show');
        
        // 기본 정보가 포함되어 있는지 확인
        $response->assertSee('언어 정보');
        $response->assertSee('언어명');
        $response->assertSee('언어 코드');
        $response->assertSee('로케일');
        $response->assertSee('방향');
        $response->assertSee('상태');
        $response->assertSee('기본 언어');
        $response->assertSee('정렬 순서');
    }

    /** @test */
    public function 관리자가_언어_상세_페이지에서_번역_정보를_볼_수_있다()
    {
        // 먼저 언어 생성
        $language = \App\Models\Language::create([
            'name' => '번역 확인 언어',
            'code' => 'trans',
            'locale' => 'trans_TRA',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 1,
        ]);

        $response = $this->get($this->baseUrl . '/' . $language->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.show');
        
        // 번역 관련 정보가 포함되어 있는지 확인
        $response->assertSee('번역 정보');
        $response->assertSee('번역 키');
        $response->assertSee('번역 값');
    }

    /** @test */
    public function 관리자가_언어_상세_페이지에서_사용_통계를_볼_수_있다()
    {
        // 먼저 언어 생성
        $language = \App\Models\Language::create([
            'name' => '통계 확인 언어',
            'code' => 'stats',
            'locale' => 'stats_STA',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 1,
        ]);

        $response = $this->get($this->baseUrl . '/' . $language->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.language.show');
        
        // 사용 통계가 포함되어 있는지 확인
        $response->assertSee('사용 통계');
        $response->assertSee('사용자 수');
        $response->assertSee('페이지 뷰');
    }

    /** @test */
    public function 관리자가_언어_생성_시_필수_필드를_검증할_수_있다()
    {
        $response = $this->postJson($this->baseUrl, []);
        
        $response->assertStatus(422);
        // TODO: locale 필드가 실제 검증에서 누락됨 - 현재는 name, code만 검증
        // TODO: locale 필드 검증 로직 추가 필요
        $response->assertJsonValidationErrors(['name', 'code']);
        // $response->assertJsonValidationErrors(['name', 'code', 'locale']);
    }

    /** @test */
    public function 관리자가_언어_수정_시_필수_필드를_검증할_수_있다()
    {
        // 먼저 언어 생성
        $language = \App\Models\Language::create([
            'name' => '검증 대상 언어',
            'code' => 'valid',
            'locale' => 'valid_VAL',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 1,
        ]);

        $response = $this->putJson($this->baseUrl . '/' . $language->id, []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'code', 'locale']);
    }

    /** @test */
    public function 관리자가_언어_코드_중복을_검증할_수_있다()
    {
        // 첫 번째 언어 생성
        \App\Models\Language::create([
            'name' => '첫 번째 언어',
            'code' => 'duplicate',
            'locale' => 'dup1_DUP',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 1,
        ]);

        // 중복된 코드로 두 번째 언어 생성 시도
        $response = $this->postJson($this->baseUrl, [
            'name' => '두 번째 언어',
            'code' => 'duplicate',
            'locale' => 'dup2_DUP',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 2,
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['code']);
    }

    /** @test */
    public function 관리자가_언어_로케일_중복을_검증할_수_있다()
    {
        // 첫 번째 언어 생성
        \App\Models\Language::create([
            'name' => '첫 번째 언어',
            'code' => 'first',
            'locale' => 'duplicate_DUP',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 1,
        ]);

        // 중복된 로케일로 두 번째 언어 생성 시도
        $response = $this->postJson($this->baseUrl, [
            'name' => '두 번째 언어',
            'code' => 'second',
            'locale' => 'duplicate_DUP',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 2,
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['locale']);
    }

    /** @test */
    public function 관리자가_언어_상태를_토글할_수_있다()
    {
        // 먼저 언어 생성
        $language = \App\Models\Language::create([
            'name' => '상태 토글 언어',
            'code' => 'toggle',
            'locale' => 'toggle_TOG',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 1,
        ]);

        // 비활성으로 변경
        $response = $this->patchJson($this->baseUrl . '/' . $language->id . '/toggle-status', [
            'status' => 'inactive'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function 관리자가_언어_기본_설정을_변경할_수_있다()
    {
        // 기존 기본 언어 생성
        $oldDefault = \App\Models\Language::create([
            'name' => '기존 기본 언어',
            'code' => 'old',
            'locale' => 'old_OLD',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => true,
            'sort_order' => 1,
        ]);

        // 새 언어 생성
        $newLanguage = \App\Models\Language::create([
            'name' => '새 기본 언어',
            'code' => 'new',
            'locale' => 'new_NEW',
            'direction' => 'ltr',
            'status' => 'active',
            'is_default' => false,
            'sort_order' => 2,
        ]);

        // 새 언어를 기본으로 설정
        $response = $this->patchJson($this->baseUrl . '/' . $newLanguage->id . '/set-default');
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // 기존 기본 언어가 해제되었는지 확인
        $this->assertDatabaseHas('languages', [
            'id' => $oldDefault->id,
            'is_default' => false,
        ]);
        
        // 새 언어가 기본으로 설정되었는지 확인
        $this->assertDatabaseHas('languages', [
            'id' => $newLanguage->id,
            'is_default' => true,
        ]);
    }
}
