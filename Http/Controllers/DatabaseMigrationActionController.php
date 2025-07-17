<?php

namespace Jiny\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class DatabaseMigrationActionController extends Controller
{
    /**
     * 마이그레이션 실행
     */
    public function run(Request $request): JsonResponse
    {
        try {
            $output = [];
            $exitCode = Artisan::call('migrate', [], $output);
            
            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => '마이그레이션이 성공적으로 실행되었습니다.',
                    'output' => $output
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '마이그레이션 실행 중 오류가 발생했습니다.',
                    'output' => $output
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '마이그레이션 실행 중 오류가 발생했습니다: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 마이그레이션 롤백
     */
    public function rollback(Request $request): JsonResponse
    {
        try {
            $steps = $request->input('steps', 1);
            $output = [];
            $exitCode = Artisan::call('migrate:rollback', ['--step' => $steps], $output);
            
            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => '마이그레이션이 성공적으로 롤백되었습니다.',
                    'output' => $output
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '마이그레이션 롤백 중 오류가 발생했습니다.',
                    'output' => $output
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '마이그레이션 롤백 중 오류가 발생했습니다: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 마이그레이션 새로고침
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $output = [];
            $exitCode = Artisan::call('migrate:refresh', [], $output);
            
            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => '마이그레이션이 성공적으로 새로고침되었습니다.',
                    'output' => $output
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '마이그레이션 새로고침 중 오류가 발생했습니다.',
                    'output' => $output
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '마이그레이션 새로고침 중 오류가 발생했습니다: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 특정 마이그레이션 실행
     */
    public function runSpecific(Request $request, $migration): JsonResponse
    {
        try {
            $output = [];
            $exitCode = Artisan::call('migrate', ['--path' => "database/migrations/{$migration}.php"], $output);
            
            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => '마이그레이션이 성공적으로 실행되었습니다.',
                    'output' => $output
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '마이그레이션 실행 중 오류가 발생했습니다.',
                    'output' => $output
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '마이그레이션 실행 중 오류가 발생했습니다: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 마이그레이션 리셋
     */
    public function reset(Request $request): JsonResponse
    {
        try {
            $output = [];
            $exitCode = Artisan::call('migrate:reset', [], $output);
            
            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => '마이그레이션이 성공적으로 리셋되었습니다.',
                    'output' => $output
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '마이그레이션 리셋 중 오류가 발생했습니다.',
                    'output' => $output
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '마이그레이션 리셋 중 오류가 발생했습니다: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 마이그레이션 상태 확인
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $output = [];
            $exitCode = Artisan::call('migrate:status', [], $output);
            
            return response()->json([
                'success' => true,
                'status' => $exitCode === 0 ? 'ready' : 'error',
                'output' => $output,
                'pending_migrations' => $this->getPendingMigrations(),
                'ran_migrations' => $this->getRanMigrations()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '마이그레이션 상태 확인 중 오류가 발생했습니다: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 대기 중인 마이그레이션 목록 가져오기
     */
    private function getPendingMigrations(): array
    {
        try {
            $output = [];
            Artisan::call('migrate:status', [], $output);
            
            $pending = [];
            foreach ($output as $line) {
                if (str_contains($line, 'Pending')) {
                    $migration = trim(explode('Pending', $line)[0]);
                    $pending[] = $migration;
                }
            }
            
            return $pending;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 실행된 마이그레이션 목록 가져오기
     */
    private function getRanMigrations(): array
    {
        try {
            $output = [];
            Artisan::call('migrate:status', [], $output);
            
            $ran = [];
            foreach ($output as $line) {
                if (str_contains($line, 'Ran')) {
                    $migration = trim(explode('Ran', $line)[0]);
                    $ran[] = $migration;
                }
            }
            
            return $ran;
        } catch (\Exception $e) {
            return [];
        }
    }
} 