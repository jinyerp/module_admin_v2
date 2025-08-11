<?php

namespace Jiny\Admin\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Jiny\Admin\App\Services\AdminSettingService;

/**
 * AdminSettingController
 * 
 * 관리자 설정 관리를 위한 기본 컨트롤러
 * 독립적인 컨트롤러로 템플릿 메서드 패턴 구현
 * 
 * @package Jiny\Admin\App\Http\Controllers
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 */
abstract class AdminSettingController extends Controller
{
    protected $settingService;
    protected $configKey;
    protected $viewPath;

    public function __construct(AdminSettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * 설정 목록/폼 표시
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 라우트 이름 추출
        $route = $this->getRouteName($request);
        
        $view = $this->_index($request);
        
        // 템플릿에서 반환받은 뷰에 라우트 이름 추가
        return $view->with('route', $route);
    }

    /**
     * 설정 저장 (AJAX)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        return $this->_store($request);
    }

    /**
     * 설정 목록/폼 표시 (템플릿 메서드)
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    abstract protected function _index(Request $request);

    /**
     * 설정 저장 (템플릿 메서드)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    abstract protected function _store(Request $request);

    /**
     * 설정 데이터 읽기
     * 
     * @param string $key 설정 키
     * @param array $default 기본값
     * @return array
     */
    protected function getSettings($key = null, array $default = [])
    {
        $configKey = $key ?? $this->configKey;
        return $this->settingService->get($configKey, $default);
    }

    /**
     * 설정 데이터 저장
     * 
     * @param array $data 저장할 데이터
     * @param string $key 설정 키
     * @return bool
     */
    protected function saveSettings(array $data, $key = null)
    {
        $configKey = $key ?? $this->configKey;
        return $this->settingService->save($configKey, $data);
    }

    /**
     * 설정 데이터 업데이트
     * 
     * @param array $data 업데이트할 데이터
     * @param string $key 설정 키
     * @return bool
     */
    protected function updateSettings(array $data, $key = null)
    {
        $configKey = $key ?? $this->configKey;
        return $this->settingService->update($configKey, $data);
    }

    /**
     * 성공 응답 반환
     * 
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @return JsonResponse
     */
    protected function successResponse($message = '설정이 성공적으로 저장되었습니다.', array $data = [])
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * 오류 응답 반환
     * 
     * @param string $message 오류 메시지
     * @param array $errors 유효성 검사 오류
     * @param int $status HTTP 상태 코드
     * @return JsonResponse
     */
    protected function errorResponse($message = '설정 저장 중 오류가 발생했습니다.', array $errors = [], $status = 422)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * 뷰 데이터 준비
     * 
     * @param array $settings 설정 데이터
     * @return array
     */
    protected function prepareViewData(array $settings = [])
    {
        return [
            'settings' => $settings,
            'route' => $this->getRouteName(request())
        ];
    }

    /**
     * 라우트 이름 추출
     * 
     * @param Request $request
     * @return string
     */
    protected function getRouteName(Request $request)
    {
        $route = $request->route()->getName();
        $route = substr($route, 0, strrpos($route, '.')).".";
        return $route;
    }
} 