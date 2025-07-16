<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AdminPermission 모델
 *
 * 시스템에서 사용할 수 있는 모든 권한을 정의하는 모델입니다.
 *
 * 주요 기능:
 * - 권한 정의 및 관리 (시스템 운영)
 * - 모듈별 권한 분류 (관리 효율성)
 * - 권한 활성화/비활성화 상태 관리 (보안 강화)
 * - 권한별 설명 및 표시명 관리 (사용자 친화성)
 *
 * 관계: admin_user_permissions 테이블을 통해 관리자와 연결
 *
 * 도메인 지식:
 * - 권한은 시스템 보안의 핵심 요소
 * - 모듈별 분류는 권한 관리의 효율성을 높임
 * - 권한 설명은 감사 및 교육에 필수
 */
class AdminPermission extends Model
{
    use HasFactory;

    /**
     * 테이블명
     */
    protected $table = 'admin_permissions';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'name',           // 권한명 (예: user.create)
        'display_name',   // 표시명 (예: 사용자 생성)
        'description',    // 설명
        'module',         // 모듈명 (예: user, country, admin)
        'is_active',      // 활성화 상태
        'sort_order',     // 정렬 순서
    ];

    /**
     * 타입 캐스팅 설정
     */
    protected $casts = [
        'is_active' => 'boolean',
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
     * 모듈 표시명
     *
     * @return string
     */
    public function getModuleDisplayNameAttribute(): string
    {
        $moduleNames = [
            'user' => '사용자 관리',
            'country' => '국가 관리',
            'admin' => '관리자 관리',
            'system' => '시스템 관리',
            'auth' => '인증 관리',
            'log' => '로그 관리',
        ];

        return $moduleNames[$this->module] ?? ucfirst($this->module);
    }

    /**
     * 권한 활성 여부
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->attributes['is_active'] ?? false;
    }

    /**
     * 스코프 (Scope)
     */

    /**
     * 활성 권한만 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 비활성 권한만 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * 특정 모듈의 권한 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $module
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * 정렬 순서로 정렬
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('display_name');
    }

    /**
     * 관계 (Relationships)
     */

    /**
     * 이 권한을 가진 관리자들
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userPermissions(): HasMany
    {
        return $this->hasMany(AdminUserPermission::class, 'permission_id');
    }

    /**
     * 정적 메서드 (Static Methods)
     */

    /**
     * 활성 권한 목록 조회
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->ordered()->get();
    }

    /**
     * 특정 모듈의 활성 권한 조회
     *
     * @param string $module
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByModule(string $module): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->byModule($module)->ordered()->get();
    }

    /**
     * 모듈별 권한 통계
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getStats(): \Illuminate\Database\Eloquent\Collection
    {
        return static::selectRaw('
            module,
            COUNT(*) as total,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
        ')
        ->groupBy('module')
        ->orderBy('module')
        ->get();
    }

    /**
     * 권한명으로 권한 찾기
     *
     * @param string $name
     * @return self|null
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * 인스턴스 메서드 (Instance Methods)
     */

    /**
     * 권한 활성화
     *
     * @return bool
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * 권한 비활성화
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * 권한 정보 업데이트
     *
     * @param array $data
     * @return bool
     */
    public function updatePermissionInfo(array $data): bool
    {
        return $this->update($data);
    }

    /**
     * 권한 사용 가능 여부 확인
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->is_active;
    }

    /**
     * 권한 삭제 가능 여부 확인
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        // 이 권한을 사용하는 관리자가 있는지 확인
        return $this->userPermissions()->count() === 0;
    }
}
