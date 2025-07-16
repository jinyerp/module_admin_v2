<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * 관리자 회원 모델
 *
 * - 관리자 전용 인증 및 정보 관리
 * - 슈퍼관리자(super), 일반관리자(admin), 스태프(staff) 등 다양한 등급 지원
 * - 별도의 admin_users 테이블을 사용하여 보안 및 관리 분리
 */
class AdminUser extends Authenticatable
{
    /**
     * 테이블명
     * @var string
     */
    protected $table = 'admin_users';

    /**
     * PK 타입 및 auto-increment 사용
     */
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    /**
     * 대량 할당 가능 필드
     * @var array
     */
    protected $fillable = [
        'name', // 관리자 이름
        'email', // 관리자 이메일(로그인)
        'password', // 비밀번호(해시)
        'type', // 관리자 등급(super, admin, staff 등)
        'status', // 계정 상태(active, inactive, suspended 등)
        'last_login_at', // 마지막 로그인 일시
        'login_count', // 로그인 횟수
        'is_verified', // 이메일 인증 여부
        'email_verified_at', // 이메일 인증 일시
        'phone', // 연락처(선택)
        'avatar', // 프로필 이미지(선택)
        'memo', // 관리자 메모(선택)
        'remember_token' // 자동 로그인 토큰
    ];

    /**
     * 숨김 처리 필드
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * 타입 캐스팅
     * @var array
     */
    protected $casts = [
        'is_verified' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    // === 도메인 지식 ===
    // - 관리자 등급(type):
    //   * super: 시스템 전체 권한, 모든 관리자 관리 가능
    //   * admin: 일반 관리 권한, 일부 시스템 설정 가능
    //   * staff: 제한적 관리 권한, 주로 운영 지원
    // - status: active(활성), inactive(비활성), suspended(정지)
    // - is_verified: 이메일 인증 여부(보안 강화)
    // - login_count, last_login_at: 보안 모니터링 및 감사 용도
    // - memo: 내부 관리용 메모(예: 권한 변경 이력 등)
}
