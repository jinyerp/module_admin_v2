<?php

namespace Jiny\Admin\App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

/**
 * AdminSettingService
 * 
 * 관리자 설정 관리를 위한 서비스 클래스
 * 
 * @package Jiny\Admin\App\Services
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 */
class AdminSettingService
{
    /**
     * 설정값 읽기
     * 
     * @param string $key 설정 키 (예: 'admin.auth')
     * @param mixed $default 기본값
     * @return mixed
     */
    public function get($key, $default = null)
    {
        // config() 함수로 설정값 읽기
        $value = config($key, $default);
        
        // config()에서 null이 반환되면 파일에서 직접 읽기 시도
        if ($value === null) {
            $filePath = $this->keyToFilePath($key);
            if (File::exists($filePath)) {
                $value = require $filePath;
            }
        }
        
        return $value ?? $default;
    }

    /**
     * 설정값 설정 (메모리상에서만)
     * 
     * @param string $key 설정 키
     * @param mixed $value 설정값
     * @return void
     */
    public function set($key, $value)
    {
        Config::set($key, $value);
    }

    /**
     * 설정값을 파일에 저장
     * 
     * @param string $key 설정 키 (예: 'admin.auth')
     * @param array $data 저장할 데이터
     * @return bool
     */
    public function save($key, array $data)
    {
        try {
            // 키를 파일 경로로 변환
            $filePath = $this->keyToFilePath($key);
            
            // 디렉토리가 없으면 생성
            $directory = dirname($filePath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // PHP 배열 형태로 변환
            $content = $this->arrayToPhp($data);
            
            // 파일에 저장
            File::put($filePath, $content);
            
            // 설정 캐시 클리어
            $this->clearConfigCache();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('설정 저장 실패: ' . $e->getMessage(), [
                'key' => $key,
                'file_path' => $filePath ?? null
            ]);
            return false;
        }
    }

    /**
     * 설정값 업데이트 (기존 설정과 병합)
     * 
     * @param string $key 설정 키
     * @param array $data 업데이트할 데이터
     * @return bool
     */
    public function update($key, array $data)
    {
        try {
            // 기존 설정 읽기 (config() 함수 우선 사용)
            $existingData = config($key, []);
            
            // config()에서 null이 반환되면 파일에서 직접 읽기
            if ($existingData === null) {
                $filePath = $this->keyToFilePath($key);
                if (File::exists($filePath)) {
                    $existingData = require $filePath;
                } else {
                    $existingData = [];
                }
            }
            
            // 데이터 병합
            $mergedData = $this->mergeArrays($existingData, $data);
            
            // 저장
            return $this->save($key, $mergedData);
        } catch (\Exception $e) {
            \Log::error('설정 업데이트 실패: ' . $e->getMessage(), [
                'key' => $key
            ]);
            return false;
        }
    }

    /**
     * 설정 키를 파일 경로로 변환
     * 
     * @param string $key 설정 키 (예: 'admin.auth')
     * @return string 파일 경로
     */
    private function keyToFilePath($key)
    {
        // 키를 점으로 분리
        $parts = explode('.', $key);
        
        // config 디렉토리 경로
        $configPath = config_path();
        
        // 파일명 생성
        $fileName = array_pop($parts) . '.php';
        
        // 디렉토리 경로 생성
        $directory = implode('/', $parts);
        
        return $configPath . '/' . $directory . '/' . $fileName;
    }

    /**
     * 배열을 PHP 코드로 변환
     * 
     * @param array $data
     * @return string
     */
    private function arrayToPhp(array $data)
    {
        // boolean 값을 올바르게 처리하기 위해 먼저 배열을 순회하여 변환
        $data = $this->convertBooleanValues($data);
        
        $content = "<?php\n\nreturn ";
        $phpCode = var_export($data, true);
        
        // array() 함수를 [] 문법으로 변경
        $phpCode = preg_replace('/array\s*\(/s', '[', $phpCode);
        $phpCode = preg_replace('/\)\s*,\s*$/m', '],', $phpCode);
        $phpCode = preg_replace('/\)\s*$/m', ']', $phpCode);
        
        // 전체 들여쓰기를 4칸으로 통일
        $phpCode = $this->normalizeIndentation($phpCode);
        
        $content .= $phpCode;
        $content .= ";\n";
        
        return $content;
    }

    /**
     * 배열 내의 boolean 값을 올바르게 변환
     * 
     * @param array $data
     * @return array
     */
    private function convertBooleanValues(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->convertBooleanValues($value);
            } else {
                // boolean 값 체크 및 변환
                if ($value === true || $value === false) {
                    $data[$key] = $value;
                } elseif ($value === '1' || $value === 1) {
                    $data[$key] = true;
                } elseif ($value === '0' || $value === 0) {
                    $data[$key] = false;
                }
            }
        }
        
        return $data;
    }

    /**
     * 들여쓰기를 4칸으로 정규화
     * 
     * @param string $code
     * @return string
     */
    private function normalizeIndentation($code)
    {
        $lines = explode("\n", $code);
        $normalizedLines = [];
        
        foreach ($lines as $line) {
            // 빈 줄은 그대로 유지
            if (trim($line) === '') {
                $normalizedLines[] = $line;
                continue;
            }
            
            // 현재 들여쓰기 레벨 계산
            $indentLevel = 0;
            $trimmedLine = ltrim($line);
            $originalIndent = strlen($line) - strlen($trimmedLine);
            
            // 2칸 단위로 들여쓰기 레벨 계산
            $indentLevel = $originalIndent / 2;
            
            // 4칸 단위로 새로운 들여쓰기 생성
            $newIndent = str_repeat('    ', $indentLevel);
            
            $normalizedLines[] = $newIndent . $trimmedLine;
        }
        
        return implode("\n", $normalizedLines);
    }

    /**
     * 배열 병합 (재귀적)
     * 
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function mergeArrays(array $array1, array $array2)
    {
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                $array1[$key] = $this->mergeArrays($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }
        
        return $array1;
    }

    /**
     * 설정 캐시 클리어
     * 
     * @return void
     */
    private function clearConfigCache()
    {
        try {
            \Artisan::call('config:clear');
        } catch (\Exception $e) {
            \Log::warning('설정 캐시 클리어 실패: ' . $e->getMessage());
        }
    }

    /**
     * 설정 파일 존재 여부 확인
     * 
     * @param string $key 설정 키
     * @return bool
     */
    public function hasFile($key)
    {
        $filePath = $this->keyToFilePath($key);
        return File::exists($filePath);
    }

    /**
     * 설정 파일 경로 반환
     * 
     * @param string $key 설정 키
     * @return string
     */
    public function getFilePath($key)
    {
        return $this->keyToFilePath($key);
    }
} 