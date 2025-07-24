<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Country 모델
 *
 * 시스템에서 지원하는 모든 국가 정보를 관리하는 모델입니다.
 *
 * 주요 기능:
 * - ISO 3166-1 표준 국가 코드 및 국가명 관리 (국제 표준)
 * - 국가별 통화, 언어, 시간대 정보 관리 (다국가 서비스)
 * - 국가별 활성화 상태 및 기본 국가 설정 (시스템 관리)
 * - 국가별 메타데이터 및 확장 정보 저장 (유연한 시스템)
 * - 다국가 서비스의 국가 선택 옵션 제공 (사용자 경험)
 *
 * 다국가 서비스 목적: 국제화 지원, 지역별 서비스 제공, 현지화 전략
 *
 * 도메인 지식:
 * - ISO 3166-1은 국제 국가 표준으로 전 세계에서 인정
 * - 국가별 통화와 언어는 다국가 서비스의 핵심 요소
 * - 기본 국가는 시스템의 기본 국가로 서비스 지역의 기준
 * - 국가별 정렬 순서는 사용자 인터페이스의 국가 선택 메뉴에 활용
 */
class Country extends Model
{
    use HasFactory;

    /**
     * 테이블명
     */
    protected $table = 'admin_country';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'name',           // 국가명 (국가의 전체 이름, 예: South Korea, United States)
        'code',           // 국가 코드 (ISO 3166-1 alpha-2, 예: KR, US, JP)
        'code3',          // 국가 코드 3자리 (ISO 3166-1 alpha-3, 예: KOR, USA, JPN)
        'flag',           // 국기 아이콘 (국가별 국기 아이콘 파일명)
        'currency_code',  // 기본 통화 코드 (해당 국가의 기본 통화, 예: KRW, USD)
        'language_code',  // 기본 언어 코드 (해당 국가의 기본 언어, 예: ko, en)
        'timezone',       // 기본 시간대 (해당 국가의 기본 시간대, 예: Asia/Seoul)
        'phone_code',     // 전화 국가 코드 (해당 국가의 전화 국가 코드, 예: +82)
        'is_active',      // 활성화 여부 (현재 사용 가능한 국가인지 여부)
        'is_default',     // 기본 국가 여부 (시스템의 기본 국가)
        'sort_order',     // 정렬 순서 (UI에서 표시되는 순서)
        'metadata',       // 추가 메타데이터 (JSON 형태, 확장 가능한 구조)
        'enable',         // 추가
    ];

    /**
     * 타입 캐스팅 설정
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
        'enable' => 'boolean', // 추가
    ];

    /**
     * 숨겨진 속성들 (JSON 직렬화 시 제외)
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * 액세서 (Accessor)
     */

    /**
     * 국가 전체 정보 가져오기 (이름 + 코드)
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }

    /**
     * 메타데이터에서 지역 정보 가져오기
     *
     * @return string|null
     */
    public function getRegionAttribute(): ?string
    {
        return $this->metadata['region'] ?? null;
    }

    /**
     * 메타데이터에서 대륙 정보 가져오기
     *
     * @return string|null
     */
    public function getContinentAttribute(): ?string
    {
        return $this->metadata['continent'] ?? null;
    }

    /**
     * 메타데이터에서 설명 정보 가져오기
     *
     * @return string|null
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->metadata['description'] ?? null;
    }

    /**
     * 스코프 (Scope)
     */

    /**
     * 활성화된 국가만 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 기본 국가 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * 특정 지역의 국가 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $region
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRegion($query, string $region)
    {
        return $query->whereJsonContains('metadata->region', $region);
    }

    /**
     * 특정 대륙의 국가 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $continent
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByContinent($query, string $continent)
    {
        return $query->whereJsonContains('metadata->continent', $continent);
    }

    /**
     * 특정 통화를 사용하는 국가 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $currencyCode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCurrency($query, string $currencyCode)
    {
        return $query->where('currency_code', $currencyCode);
    }

    /**
     * 특정 언어를 사용하는 국가 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $languageCode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLanguage($query, string $languageCode)
    {
        return $query->where('language_code', $languageCode);
    }

    /**
     * 정렬 순서별 정렬
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderBySort($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * 관계 (Relationships)
     */

    /**
     * 이 국가의 기본 통화
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    /**
     * 이 국가의 기본 언어
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }

    // ===== User 관련 연관관계 및 메서드 전체 제거 =====
    // public function users() { ... }
    // public function userProfiles() { ... }
    // public function userAddresses() { ... }
    // public function userContactNumbers() { ... }
    // public function userCountryStatistics() { ... }
    // public function getUserCount() { ... }
    // public function getActiveUserCount() { ... }

    /**
     * 정적 메서드 (Static Methods)
     */

    /**
     * 기본 국가 가져오기
     *
     * @return self|null
     */
    public static function getDefault(): ?self
    {
        return static::default()->first();
    }

    /**
     * 활성화된 국가 목록 가져오기
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->orderBySort()->get();
    }

    /**
     * 국가 코드로 국가 찾기
     *
     * @param string $code
     * @return self|null
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * 국가 코드 3자리로 국가 찾기
     *
     * @param string $code3
     * @return self|null
     */
    public static function findByCode3(string $code3): ?self
    {
        return static::where('code3', $code3)->first();
    }

    /**
     * 국가명으로 국가 찾기
     *
     * @param string $name
     * @return self|null
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * 특정 지역의 국가들 가져오기
     *
     * @param string $region
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByRegion(string $region): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->byRegion($region)->orderBySort()->get();
    }

    /**
     * 특정 대륙의 국가들 가져오기
     *
     * @param string $continent
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByContinent(string $continent): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->byContinent($continent)->orderBySort()->get();
    }

    /**
     * 특정 통화를 사용하는 국가들 가져오기
     *
     * @param string $currencyCode
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByCurrency(string $currencyCode): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->byCurrency($currencyCode)->orderBySort()->get();
    }

    /**
     * 특정 언어를 사용하는 국가들 가져오기
     *
     * @param string $languageCode
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByLanguage(string $languageCode): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->byLanguage($languageCode)->orderBySort()->get();
    }

    /**
     * 인스턴스 메서드 (Instance Methods)
     */

    /**
     * 국가를 기본 국가로 설정
     *
     * @return bool
     */
    public function setAsDefault(): bool
    {
        // 기존 기본 국가 해제
        static::where('is_default', true)->update(['is_default' => false]);

        // 현재 국가를 기본 국가로 설정
        return $this->update(['is_default' => true]);
    }

    /**
     * 국가 활성화/비활성화
     *
     * @param bool $active
     * @return bool
     */
    public function setActive(bool $active = true): bool
    {
        return $this->update(['is_active' => $active]);
    }

    /**
     * 정렬 순서 설정
     *
     * @param int $order
     * @return bool
     */
    public function setSortOrder(int $order): bool
    {
        return $this->update(['sort_order' => $order]);
    }

    /**
     * 국가 정보 업데이트
     *
     * @param array $data
     * @return bool
     */
    public function updateCountryInfo(array $data): bool
    {
        return $this->update($data);
    }

    /**
     * 라우트 키 이름 가져오기
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
