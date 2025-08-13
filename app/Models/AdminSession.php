<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * AdminSession 모델
 *
 * 관리자 세션 정보를 관리하는 모델
 * AdminUser와 1:N 관계를 가지며, 세션 추적 및 보안 모니터링에 사용
 *
 * @package Jiny\Admin\App\Models
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminSession.md
 */
class AdminSession extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Jiny\Admin\Database\Factories\AdminSessionFactory::new();
    }

    /**
     * 테이블 이름
     */
    protected $table = 'admin_sessions';

    /**
     * 기본 키
     */
    protected $primaryKey = 'session_id';

    /**
     * 기본 키 타입
     */
    protected $keyType = 'string';

    /**
     * 자동 증가 사용 안함
     */
    public $incrementing = false;

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'session_id',
        'admin_user_id',
        'admin_name',
        'admin_email',
        'admin_type',
        'ip_address',
        'user_agent',
        'login_location',
        'device',
        'login_at',
        'last_activity',
        'is_active',
    ];

    /**
     * 날짜로 처리할 속성들
     */
    protected $dates = [
        'login_at',
        'last_activity',
        'created_at',
        'updated_at',
    ];

    /**
     * 캐스팅할 속성들
     */
    protected $casts = [
        'login_at' => 'datetime',
        'last_activity' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 숨길 속성들
     */
    protected $hidden = [
        'session_id',
    ];

    /**
     * 접근자로 추가할 속성들
     */
    protected $appends = [
        'is_active',
        'duration',
        'duration_human',
    ];

    /**
     * AdminUser와의 관계
     * 세션은 하나의 관리자 사용자에 속함
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    /**
     * 세션이 활성 상태인지 확인
     * 30분 이상 활동이 없으면 비활성으로 간주
     */
    public function getIsActiveAttribute(): bool
    {
        if (!$this->last_activity) {
            return false;
        }
        
        return $this->last_activity->isAfter(now()->subMinutes(30));
    }

    /**
     * 세션 지속 시간 (초)
     */
    public function getDurationAttribute(): int
    {
        if (!$this->login_at) {
            return 0;
        }
        
        $endTime = $this->last_activity ?: now();
        return $endTime->diffInSeconds($this->login_at);
    }

    /**
     * 세션 지속 시간 (사람이 읽기 쉬운 형태)
     */
    public function getDurationHumanAttribute(): string
    {
        if (!$this->login_at) {
            return 'N/A';
        }
        
        $endTime = $this->last_activity ?: now();
        $duration = $endTime->diff($this->login_at);
        
        if ($duration->days > 0) {
            return $duration->days . '일 ' . $duration->h . '시간 ' . $duration->i . '분';
        } elseif ($duration->h > 0) {
            return $duration->h . '시간 ' . $duration->i . '분';
        } elseif ($duration->i > 0) {
            return $duration->i . '분 ' . $duration->s . '초';
        } else {
            return $duration->s . '초';
        }
    }

    /**
     * 세션을 활성화 (last_activity 업데이트)
     */
    public function activate(): bool
    {
        $this->last_activity = now();
        return $this->save();
    }

    /**
     * 세션을 비활성화 (last_activity를 null로 설정)
     */
    public function deactivate(): bool
    {
        $this->last_activity = null;
        return $this->save();
    }

    /**
     * 세션이 만료되었는지 확인
     * 기본 만료 시간은 30분
     */
    public function isExpired(int $expiryMinutes = 30): bool
    {
        if (!$this->last_activity) {
            return true;
        }
        
        return $this->last_activity->isBefore(now()->subMinutes($expiryMinutes));
    }

    /**
     * 세션을 새로고침 (last_activity를 현재 시간으로 업데이트)
     */
    public function refresh(): bool
    {
        return $this->activate();
    }

    /**
     * IP 주소가 변경되었는지 확인
     */
    public function hasIpChanged(string $currentIp): bool
    {
        return $this->ip_address !== $currentIp;
    }

    /**
     * 사용자 에이전트가 변경되었는지 확인
     */
    public function hasUserAgentChanged(string $currentUserAgent): bool
    {
        return $this->user_agent !== $currentUserAgent;
    }

    /**
     * 의심스러운 활동인지 확인
     * IP 주소나 사용자 에이전트가 변경된 경우
     */
    public function isSuspicious(string $currentIp, string $currentUserAgent): bool
    {
        return $this->hasIpChanged($currentIp) || $this->hasUserAgentChanged($currentUserAgent);
    }

    /**
     * 세션 정보를 안전하게 반환 (민감한 정보 제거)
     */
    public function toSafeArray(): array
    {
        $data = $this->toArray();
        
        // 민감한 정보 마스킹
        if (isset($data['session_id'])) {
            $data['session_id'] = substr($data['session_id'], 0, 8) . '...';
        }
        
        if (isset($data['user_agent'])) {
            $data['user_agent'] = $this->truncateUserAgent($data['user_agent']);
        }
        
        return $data;
    }

    /**
     * 사용자 에이전트를 축약하여 반환
     */
    private function truncateUserAgent(string $userAgent): string
    {
        if (strlen($userAgent) <= 100) {
            return $userAgent;
        }
        
        return substr($userAgent, 0, 100) . '...';
    }

    /**
     * 스코프: 활성 세션만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('last_activity', '>', now()->subMinutes(30));
    }

    /**
     * 스코프: 비활성 세션만 조회
     */
    public function scopeInactive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_activity')
              ->orWhere('last_activity', '<=', now()->subMinutes(30));
        });
    }

    /**
     * 스코프: 특정 관리자 사용자의 세션만 조회
     */
    public function scopeByAdminUser($query, int $adminUserId)
    {
        return $query->where('admin_user_id', $adminUserId);
    }

    /**
     * 스코프: 특정 IP 주소의 세션만 조회
     */
    public function scopeByIpAddress($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * 스코프: 특정 날짜 범위의 세션만 조회
     */
    public function scopeByDateRange($query, string $fromDate, string $toDate)
    {
        return $query->whereBetween('login_at', [$fromDate, $toDate]);
    }

    /**
     * 스코프: 만료된 세션만 조회
     */
    public function scopeExpired($query, int $expiryMinutes = 30)
    {
        return $query->where('last_activity', '<', now()->subMinutes($expiryMinutes));
    }

    /**
     * 정적 메서드: 만료된 세션 정리
     */
    public static function cleanupExpired(int $expiryMinutes = 30): int
    {
        $expiredSessions = static::expired($expiryMinutes)->get();
        $deletedCount = $expiredSessions->count();
        
        foreach ($expiredSessions as $session) {
            $session->delete();
        }
        
        return $deletedCount;
    }

    /**
     * 정적 메서드: 특정 관리자 사용자의 모든 세션 삭제
     */
    public static function deleteByAdminUser(int $adminUserId): int
    {
        $sessions = static::byAdminUser($adminUserId)->get();
        $deletedCount = $sessions->count();
        
        foreach ($sessions as $session) {
            $session->delete();
        }
        
        return $deletedCount;
    }

    /**
     * 정적 메서드: 동시 접속자 수 조회
     */
    public static function getActiveSessionCount(): int
    {
        return static::active()->count();
    }

    /**
     * 정적 메서드: 특정 관리자 사용자의 활성 세션 수 조회
     */
    public static function getActiveSessionCountByUser(int $adminUserId): int
    {
        return static::active()->byAdminUser($adminUserId)->count();
    }

    /**
     * 부트 메서드: 모델 이벤트 등록
     */
    protected static function boot()
    {
        parent::boot();

        // 모델 생성 시
        static::creating(function ($session) {
            if (!$session->login_at) {
                $session->login_at = now();
            }
            
            if (!$session->last_activity) {
                $session->last_activity = now();
            }
        });

        // 모델 업데이트 시
        static::updating(function ($session) {
            // last_activity가 변경되지 않은 경우 현재 시간으로 설정
            if ($session->isDirty('last_activity') && !$session->last_activity) {
                $session->last_activity = now();
            }
        });

        // 모델 삭제 시
        static::deleting(function ($session) {
            // 관련 로그나 감사 데이터 정리
            // 필요에 따라 구현
        });
    }
}
