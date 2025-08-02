<?php

namespace Jiny\Admin\Helpers;

use Carbon\Carbon;

class TimeHelper
{
    /**
     * 한글 시간 표현으로 변환
     * 
     * @param Carbon $carbon
     * @return string
     */
    public static function formatKoreanTime(Carbon $carbon): string
    {
        $now = Carbon::now();
        $diff = $carbon->diffInSeconds($now);
        
        if ($diff < 60) {
            return '방금 전';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . '분 전';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . '시간 전';
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . '일 전';
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . '개월 전';
        } else {
            $years = floor($diff / 31536000);
            return $years . '년 전';
        }
    }
    
    /**
     * 한글 시간 표현 (미래 시간 처리 포함)
     * 
     * @param Carbon $carbon
     * @return string
     */
    public static function formatKoreanTimeSafe(Carbon $carbon): string
    {
        $now = Carbon::now();
        
        // 미래 시간인 경우
        if ($carbon->isAfter($now)) {
            return '방금 전';
        }
        
        return self::formatKoreanTime($carbon);
    }
    
    /**
     * 상세한 한글 시간 표현
     * 
     * @param Carbon $carbon
     * @return string
     */
    public static function formatKoreanTimeDetailed(Carbon $carbon): string
    {
        $now = Carbon::now();
        $diff = $carbon->diffInSeconds($now);
        
        if ($diff < 60) {
            return '방금 전';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            $seconds = $diff % 60;
            if ($seconds > 0) {
                return $minutes . '분 ' . $seconds . '초 전';
            }
            return $minutes . '분 전';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            $minutes = floor(($diff % 3600) / 60);
            if ($minutes > 0) {
                return $hours . '시간 ' . $minutes . '분 전';
            }
            return $hours . '시간 전';
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            $hours = floor(($diff % 86400) / 3600);
            if ($hours > 0) {
                return $days . '일 ' . $hours . '시간 전';
            }
            return $days . '일 전';
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            $days = floor(($diff % 2592000) / 86400);
            if ($days > 0) {
                return $months . '개월 ' . $days . '일 전';
            }
            return $months . '개월 전';
        } else {
            $years = floor($diff / 31536000);
            $months = floor(($diff % 31536000) / 2592000);
            if ($months > 0) {
                return $years . '년 ' . $months . '개월 전';
            }
            return $years . '년 전';
        }
    }
} 