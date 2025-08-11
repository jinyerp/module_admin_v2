#!/bin/bash

# Jiny Admin 모듈 테스트 실행 스크립트
# 사용법: ./run-tests.sh [옵션]

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 기본 설정
TEST_PATH="jiny/admin/test"
OUTPUT_DIR="test-results"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# 함수: 도움말 출력
show_help() {
    echo "Jiny Admin 모듈 테스트 실행 스크립트"
    echo ""
    echo "사용법: $0 [옵션]"
    echo ""
    echo "옵션:"
    echo "  -h, --help          이 도움말을 표시합니다"
    echo "  -a, --all           모든 테스트를 실행합니다"
    echo "  -w, --web           웹 기능 테스트만 실행합니다"
    echo "  -c, --console       콘솔 명령 테스트만 실행합니다"
    echo "  -f, --filter PATTERN 특정 패턴의 테스트만 실행합니다"
    echo "  -v, --verbose       상세한 출력을 표시합니다"
    echo "  -r, --report        HTML 리포트를 생성합니다"
    echo "  -c, --coverage      코드 커버리지를 측정합니다"
    echo ""
    echo "예시:"
    echo "  $0 -a                    # 모든 테스트 실행"
    echo "  $0 -w                    # 웹 테스트만 실행"
    echo "  $0 -f AdminSession       # AdminSession 관련 테스트만 실행"
    echo "  $0 -r                    # HTML 리포트 생성"
}

# 함수: 테스트 실행
run_tests() {
    local test_filter="$1"
    local verbose_flag="$2"
    
    echo -e "${BLUE}🧪 Jiny Admin 모듈 테스트 실행 중...${NC}"
    echo "테스트 경로: $TEST_PATH"
    echo "필터: ${test_filter:-'없음'}"
    echo "시간: $(date)"
    echo "----------------------------------------"
    
    # 테스트 실행
    if [ -n "$test_filter" ]; then
        php artisan test --filter="$test_filter" $verbose_flag
    else
        php artisan test $TEST_PATH $verbose_flag
    fi
    
    local exit_code=$?
    
    echo "----------------------------------------"
    if [ $exit_code -eq 0 ]; then
        echo -e "${GREEN}✅ 모든 테스트가 성공했습니다!${NC}"
    else
        echo -e "${RED}❌ 일부 테스트가 실패했습니다.${NC}"
    fi
    
    return $exit_code
}

# 함수: HTML 리포트 생성
generate_html_report() {
    echo -e "${BLUE}📊 HTML 테스트 리포트 생성 중...${NC}"
    
    # 출력 디렉토리 생성
    mkdir -p "$OUTPUT_DIR"
    
    # PHPUnit HTML 리포트 생성
    php artisan test $TEST_PATH --testdox-html="$OUTPUT_DIR/test-report-$TIMESTAMP.html"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ HTML 리포트가 생성되었습니다: $OUTPUT_DIR/test-report-$TIMESTAMP.html${NC}"
    else
        echo -e "${RED}❌ HTML 리포트 생성에 실패했습니다.${NC}"
    fi
}

# 함수: 코드 커버리지 측정
measure_coverage() {
    echo -e "${BLUE}📈 코드 커버리지 측정 중...${NC}"
    
    # 출력 디렉토리 생성
    mkdir -p "$OUTPUT_DIR"
    
    # PHPUnit 커버리지 리포트 생성
    php artisan test $TEST_PATH --coverage-html="$OUTPUT_DIR/coverage-$TIMESTAMP"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ 커버리지 리포트가 생성되었습니다: $OUTPUT_DIR/coverage-$TIMESTAMP/index.html${NC}"
    else
        echo -e "${RED}❌ 커버리지 리포트 생성에 실패했습니다.${NC}"
    fi
}

# 함수: 테스트 상태 요약
show_test_summary() {
    echo -e "${BLUE}📋 테스트 상태 요약${NC}"
    echo "----------------------------------------"
    
    # 전체 테스트 수 계산
    local total_tests=$(php artisan test $TEST_PATH --testdox | grep -c "✓\|✗\|!")
    local passed_tests=$(php artisan test $TEST_PATH --testdox | grep -c "✓")
    local failed_tests=$(php artisan test $TEST_PATH --testdox | grep -c "✗")
    local risky_tests=$(php artisan test $TEST_PATH --testdox | grep -c "!")
    
    echo "전체 테스트: $total_tests"
    echo -e "성공: ${GREEN}$passed_tests${NC}"
    echo -e "실패: ${RED}$failed_tests${NC}"
    echo -e "주의: ${YELLOW}$risky_tests${NC}"
    
    # 성공률 계산
    if [ $total_tests -gt 0 ]; then
        local success_rate=$((passed_tests * 100 / total_tests))
        echo "성공률: $success_rate%"
    fi
}

# 메인 로직
main() {
    local test_filter=""
    local verbose_flag=""
    local generate_report=false
    local measure_coverage=false
    local run_all=false
    local run_web=false
    local run_console=false
    
    # 인수 파싱
    while [[ $# -gt 0 ]]; do
        case $1 in
            -h|--help)
                show_help
                exit 0
                ;;
            -a|--all)
                run_all=true
                shift
                ;;
            -w|--web)
                run_web=true
                shift
                ;;
            -c|--console)
                run_console=true
                shift
                ;;
            -f|--filter)
                test_filter="$2"
                shift 2
                ;;
            -v|--verbose)
                verbose_flag="--testdox"
                shift
                ;;
            -r|--report)
                generate_report=true
                shift
                ;;
            --coverage)
                measure_coverage=true
                shift
                ;;
            *)
                echo -e "${RED}알 수 없는 옵션: $1${NC}"
                show_help
                exit 1
                ;;
        esac
    done
    
    # 기본 동작: 모든 테스트 실행
    if [ "$run_all" = true ] || ([ "$run_web" = false ] && [ "$run_console" = false ] && [ -z "$test_filter" ]); then
        run_tests "" "$verbose_flag"
    fi
    
    # 웹 테스트만 실행
    if [ "$run_web" = true ]; then
        run_tests "AdminSessionLoginTest" "$verbose_flag"
    fi
    
    # 콘솔 명령 테스트만 실행
    if [ "$run_console" = true ]; then
        run_tests "AdminConsoleCommandsTest" "$verbose_flag"
    fi
    
    # 특정 필터로 테스트 실행
    if [ -n "$test_filter" ]; then
        run_tests "$test_filter" "$verbose_flag"
    fi
    
    # HTML 리포트 생성
    if [ "$generate_report" = true ]; then
        generate_html_report
    fi
    
    # 코드 커버리지 측정
    if [ "$measure_coverage" = true ]; then
        measure_coverage
    fi
    
    # 테스트 상태 요약
    show_test_summary
}

# 스크립트 실행
main "$@"
