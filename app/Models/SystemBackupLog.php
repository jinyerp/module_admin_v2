<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * 시스템 백업 로그 모델
 *
 * 이 모델은 시스템의 모든 백업 활동을 상세히 기록합니다.
 * - 데이터베이스 및 파일 백업 이력 추적 (데이터 보호)
 * - 백업 성공/실패 상태 및 성능 모니터링 (백업 관리)
 * - 백업 파일 무결성 검증 (체크섬, 파일 크기)
 * - 백업 보안 설정 관리 (암호화, 압축)
 * - 백업 저장 위치 및 접근 권한 관리 (보안 강화)
 *
 * 보안 목적: 데이터 보호, 재해 복구, 규정 준수
 */
class SystemBackupLog extends Model
{
    use HasFactory;

    /**
     * 테이블명
     */
    protected $table = 'system_backup_logs';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'backup_type',
        'backup_name',
        'file_path',
        'file_size',
        'checksum',
        'status',
        'started_at',
        'completed_at',
        'duration_seconds',
        'error_message',
        'initiated_by',
        'storage_location',
        'is_encrypted',
        'is_compressed',
        'metadata',
    ];

    /**
     * 캐스팅할 속성들
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_seconds' => 'integer',
        'is_encrypted' => 'boolean',
        'is_compressed' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * 백업 타입 상수
     */
    const TYPE_DATABASE = 'database';
    const TYPE_FILES = 'files';
    const TYPE_FULL = 'full';

    /**
     * 상태 상수
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * 백업 타입 목록
     */
    public static function getBackupTypes(): array
    {
        return [
            self::TYPE_DATABASE => '데이터베이스',
            self::TYPE_FILES => '파일',
            self::TYPE_FULL => '전체',
        ];
    }

    /**
     * 상태 목록
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => '대기',
            self::STATUS_IN_PROGRESS => '진행중',
            self::STATUS_COMPLETED => '완료',
            self::STATUS_FAILED => '실패',
            self::STATUS_CANCELLED => '취소',
        ];
    }

    /**
     * 백업을 시작한 관리자와의 관계
     */
    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(\Jiny\Admin\App\Models\AdminUser::class, 'initiated_by');
    }

    /**
     * 완료된 백업만 조회하는 스코프
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * 실패한 백업만 조회하는 스코프
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * 특정 타입의 백업만 조회하는 스코프
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('backup_type', $type);
    }

    /**
     * 암호화된 백업만 조회하는 스코프
     */
    public function scopeEncrypted(Builder $query): Builder
    {
        return $query->where('is_encrypted', true);
    }

    /**
     * 압축된 백업만 조회하는 스코프
     */
    public function scopeCompressed(Builder $query): Builder
    {
        return $query->where('is_compressed', true);
    }

    /**
     * 최근 백업만 조회하는 스코프
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * 백업 성공 여부 확인
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * 백업 실패 여부 확인
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * 백업 진행중 여부 확인
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * 백업 완료 여부 확인
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * 백업 소요 시간 (분) 계산
     */
    public function getDurationInMinutes(): float
    {
        return $this->duration_seconds ? $this->duration_seconds / 60 : 0;
    }

    /**
     * 백업 소요 시간 (시간) 계산
     */
    public function getDurationInHours(): float
    {
        return $this->duration_seconds ? $this->duration_seconds / 3600 : 0;
    }

    /**
     * 파일 크기 (MB) 계산
     */
    public function getFileSizeInMB(): float
    {
        if (!$this->file_size) {
            return 0;
        }

        // 바이트 단위로 가정하고 MB로 변환
        return $this->file_size / (1024 * 1024);
    }

    /**
     * 백업 상태에 따른 CSS 클래스 반환
     */
    public function getStatusClass(): string
    {
        return match($this->status) {
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_CANCELLED => 'secondary',
            default => 'info',
        };
    }

    /**
     * 백업 타입에 따른 아이콘 반환
     */
    public function getBackupTypeIcon(): string
    {
        return match($this->backup_type) {
            self::TYPE_DATABASE => 'database',
            self::TYPE_FILES => 'folder',
            self::TYPE_FULL => 'server',
            default => 'archive',
        };
    }

    /**
     * 백업 성공률 계산 (정적 메서드)
     */
    public static function getSuccessRate(): float
    {
        $total = self::count();
        if ($total === 0) {
            return 0;
        }

        $successful = self::where('status', self::STATUS_COMPLETED)->count();
        return round(($successful / $total) * 100, 2);
    }

    /**
     * 평균 백업 시간 계산 (정적 메서드)
     */
    public static function getAverageDuration(): float
    {
        $completed = self::where('status', self::STATUS_COMPLETED)
            ->whereNotNull('duration_seconds')
            ->avg('duration_seconds');

        return $completed ? round($completed, 2) : 0;
    }

    /**
     * 최근 백업 통계 조회 (정적 메서드)
     */
    public static function getRecentStats(int $days = 7): array
    {
        $startDate = now()->subDays($days);

        return [
            'total' => self::where('created_at', '>=', $startDate)->count(),
            'completed' => self::where('status', self::STATUS_COMPLETED)
                ->where('created_at', '>=', $startDate)->count(),
            'failed' => self::where('status', self::STATUS_FAILED)
                ->where('created_at', '>=', $startDate)->count(),
            'in_progress' => self::where('status', self::STATUS_IN_PROGRESS)
                ->where('created_at', '>=', $startDate)->count(),
        ];
    }

    /**
     * 백업 타입별 통계 조회 (정적 메서드)
     */
    public static function getStatsByType(): array
    {
        return self::selectRaw('backup_type, COUNT(*) as count, AVG(duration_seconds) as avg_duration')
            ->groupBy('backup_type')
            ->get()
            ->keyBy('backup_type')
            ->toArray();
    }

    /**
     * 백업 성능 분석 (정적 메서드)
     */
    public static function getPerformanceAnalysis(): array
    {
        $completed = self::where('status', self::STATUS_COMPLETED)
            ->whereNotNull('duration_seconds')
            ->get();

        return [
            'total_backups' => $completed->count(),
            'avg_duration' => $completed->avg('duration_seconds'),
            'min_duration' => $completed->min('duration_seconds'),
            'max_duration' => $completed->max('duration_seconds'),
            'total_size' => $completed->sum('file_size'),
            'avg_size' => $completed->avg('file_size'),
        ];
    }

    /**
     * 백업 실패 원인 분석 (정적 메서드)
     */
    public static function getFailureAnalysis(): array
    {
        $failed = self::where('status', self::STATUS_FAILED)
            ->whereNotNull('error_message')
            ->get();

        $errorPatterns = [];
        foreach ($failed as $backup) {
            $error = strtolower($backup->error_message);
            if (str_contains($error, 'disk')) {
                $errorPatterns['disk_space'] = ($errorPatterns['disk_space'] ?? 0) + 1;
            } elseif (str_contains($error, 'network')) {
                $errorPatterns['network'] = ($errorPatterns['network'] ?? 0) + 1;
            } elseif (str_contains($error, 'permission')) {
                $errorPatterns['permission'] = ($errorPatterns['permission'] ?? 0) + 1;
            } else {
                $errorPatterns['other'] = ($errorPatterns['other'] ?? 0) + 1;
            }
        }

        return $errorPatterns;
    }

    /**
     * 백업 권장사항 생성 (정적 메서드)
     */
    public static function getRecommendations(): array
    {
        $recommendations = [];

        $successRate = self::getSuccessRate();
        if ($successRate < 95) {
            $recommendations[] = '백업 성공률이 낮습니다. 백업 프로세스를 검토하세요.';
        }

        $avgDuration = self::getAverageDuration();
        if ($avgDuration > 3600) { // 1시간 이상
            $recommendations[] = '백업 시간이 오래 걸립니다. 백업 최적화를 고려하세요.';
        }

        $uncompressedBackups = self::where('is_compressed', false)
            ->where('status', self::STATUS_COMPLETED)
            ->count();

        if ($uncompressedBackups > 0) {
            $recommendations[] = '압축되지 않은 백업이 있습니다. 저장 공간을 절약하기 위해 압축을 활성화하세요.';
        }

        return $recommendations;
    }
}
