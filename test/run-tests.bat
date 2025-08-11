@echo off
REM Jiny Admin 모듈 테스트 실행 스크립트 (Windows)
REM 사용법: run-tests.bat [옵션]

setlocal enabledelayedexpansion

REM 기본 설정
set TEST_PATH=jiny\admin\test
set OUTPUT_DIR=test-results
set TIMESTAMP=%date:~0,4%%date:~5,2%%date:~8,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%

REM 색상 정의 (Windows 10 이상)
set RED=[91m
set GREEN=[92m
set YELLOW=[93m
set BLUE=[94m
set NC=[0m

REM 함수: 도움말 출력
:show_help
echo Jiny Admin 모듈 테스트 실행 스크립트
echo.
echo 사용법: %0 [옵션]
echo.
echo 옵션:
echo   -h, --help          이 도움말을 표시합니다
echo   -a, --all           모든 테스트를 실행합니다
echo   -w, --web           웹 기능 테스트만 실행합니다
echo   -c, --console       콘솔 명령 테스트만 실행합니다
echo   -f, --filter PATTERN 특정 패턴의 테스트만 실행합니다
echo   -v, --verbose       상세한 출력을 표시합니다
echo   -r, --report        HTML 리포트를 생성합니다
echo   --coverage          코드 커버리지를 측정합니다
echo.
echo 예시:
echo   %0 -a                    # 모든 테스트 실행
echo   %0 -w                    # 웹 테스트만 실행
echo   %0 -f AdminSession       # AdminSession 관련 테스트만 실행
echo   %0 -r                    # HTML 리포트 생성
goto :eof

REM 함수: 테스트 실행
:run_tests
set test_filter=%~1
set verbose_flag=%~2

echo %BLUE%🧪 Jiny Admin 모듈 테스트 실행 중...%NC%
echo 테스트 경로: %TEST_PATH%
echo 필터: %test_filter%
echo 시간: %date% %time%
echo ----------------------------------------

REM 테스트 실행
if "%test_filter%"=="" (
    php artisan test %TEST_PATH% %verbose_flag%
) else (
    php artisan test --filter="%test_filter%" %verbose_flag%
)

set exit_code=%errorlevel%

echo ----------------------------------------
if %exit_code% equ 0 (
    echo %GREEN%✅ 모든 테스트가 성공했습니다!%NC%
) else (
    echo %RED%❌ 일부 테스트가 실패했습니다.%NC%
)

exit /b %exit_code%

REM 함수: HTML 리포트 생성
:generate_html_report
echo %BLUE%📊 HTML 테스트 리포트 생성 중...%NC%

REM 출력 디렉토리 생성
if not exist "%OUTPUT_DIR%" mkdir "%OUTPUT_DIR%"

REM PHPUnit HTML 리포트 생성
php artisan test %TEST_PATH% --testdox-html="%OUTPUT_DIR%\test-report-%TIMESTAMP%.html"

if %errorlevel% equ 0 (
    echo %GREEN%✅ HTML 리포트가 생성되었습니다: %OUTPUT_DIR%\test-report-%TIMESTAMP%.html%NC%
) else (
    echo %RED%❌ HTML 리포트 생성에 실패했습니다.%NC%
)
goto :eof

REM 함수: 코드 커버리지 측정
:measure_coverage
echo %BLUE%📈 코드 커버리지 측정 중...%NC%

REM 출력 디렉토리 생성
if not exist "%OUTPUT_DIR%" mkdir "%OUTPUT_DIR%"

REM PHPUnit 커버리지 리포트 생성
php artisan test %TEST_PATH% --coverage-html="%OUTPUT_DIR%\coverage-%TIMESTAMP%"

if %errorlevel% equ 0 (
    echo %GREEN%✅ 커버리지 리포트가 생성되었습니다: %OUTPUT_DIR%\coverage-%TIMESTAMP%\index.html%NC%
) else (
    echo %RED%❌ 커버리지 리포트 생성에 실패했습니다.%NC%
)
goto :eof

REM 메인 로직
set test_filter=
set verbose_flag=
set generate_report=false
set measure_coverage=false
set run_all=false
set run_web=false
set run_console=false

REM 인수 파싱
:parse_args
if "%~1"=="" goto :execute_tests

if "%~1"=="-h" goto :show_help
if "%~1"=="--help" goto :show_help
if "%~1"=="-a" set run_all=true
if "%~1"=="--all" set run_all=true
if "%~1"=="-w" set run_web=true
if "%~1"=="--web" set run_web=true
if "%~1"=="-c" set run_console=true
if "%~1"=="--console" set run_console=true
if "%~1"=="-f" (
    set test_filter=%~2
    shift
)
if "%~1"=="--filter" (
    set test_filter=%~2
    shift
)
if "%~1"=="-v" set verbose_flag=--testdox
if "%~1"=="--verbose" set verbose_flag=--testdox
if "%~1"=="-r" set generate_report=true
if "%~1"=="--report" set generate_report=true
if "%~1"=="--coverage" set measure_coverage=true

shift
goto :parse_args

:execute_tests
REM 기본 동작: 모든 테스트 실행
if "%run_all%"=="true" (
    call :run_tests "" "%verbose_flag%"
) else if "%run_web%"=="false" if "%run_console%"=="false" if "%test_filter%"=="" (
    call :run_tests "" "%verbose_flag%"
)

REM 웹 테스트만 실행
if "%run_web%"=="true" (
    call :run_tests "AdminSessionLoginTest" "%verbose_flag%"
)

REM 콘솔 명령 테스트만 실행
if "%run_console%"=="true" (
    call :run_tests "AdminConsoleCommandsTest" "%verbose_flag%"
)

REM 특정 필터로 테스트 실행
if not "%test_filter%"=="" (
    call :run_tests "%test_filter%" "%verbose_flag%"
)

REM HTML 리포트 생성
if "%generate_report%"=="true" (
    call :generate_html_report
)

REM 코드 커버리지 측정
if "%measure_coverage%"=="true" (
    call :measure_coverage
)

echo.
echo %BLUE%📋 테스트 실행이 완료되었습니다.%NC%
echo 결과는 위의 출력을 확인하세요.
echo.
echo 자세한 정보는 다음 명령어를 사용하세요:
echo   %0 --help
echo.
pause
