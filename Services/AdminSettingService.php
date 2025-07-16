<?php

namespace Jiny\Admin\Services;

use App\Models\Admin\AdminSetting;
use Illuminate\Support\Facades\Cache;

class AdminSettingService
{
    /**
     * 설정 조회
     */
    public function getSetting(string $key, $default = null)
    {
        return Cache::remember("admin_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = AdminSetting::where('key', $key)->first();
            return $setting ? $this->castValue($setting->value, $setting->type) : $default;
        });
    }

    /**
     * 설정 저장
     */
    public function setSetting(string $key, $value, string $type = 'string', string $group = null, string $description = null, bool $isPublic = false): bool
    {
        $setting = AdminSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );

        Cache::forget("admin_setting_{$key}");
        return true;
    }

    /**
     * 그룹별 설정 조회
     */
    public function getSettingsByGroup(string $group): array
    {
        return Cache::remember("admin_settings_group_{$group}", 3600, function () use ($group) {
            return AdminSetting::where('group', $group)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => $this->castValue($setting->value, $setting->type)];
                })
                ->toArray();
        });
    }

    /**
     * 공개 설정 조회
     */
    public function getPublicSettings(): array
    {
        return Cache::remember('admin_public_settings', 3600, function () {
            return AdminSetting::where('is_public', true)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => $this->castValue($setting->value, $setting->type)];
                })
                ->toArray();
        });
    }

    /**
     * 값 타입 캐스팅
     */
    private function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return is_array($value) ? $value : explode(',', $value);
            default:
                return $value;
        }
    }
}
