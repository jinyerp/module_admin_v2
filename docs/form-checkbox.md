# Checkbox + Hidden Input 처리 방식 및 x-ui::form-checkbox 컴포넌트 사용법

## 1. HTML 체크박스의 기본 동작
- `<input type="checkbox" name="foo" value="1">`는 **체크된 경우에만** name/value가 폼 전송에 포함됩니다.
- 체크 해제 시에는 name/value가 아예 폼 전송에 포함되지 않습니다.

## 2. 서버(Laravel)에서의 처리
- 체크박스가 체크되어 있으면 `request('foo')` 값이 "1"
- 체크 해제되어 있으면 `request('foo')` 값이 **null** (아예 없음)
- DB에 boolean 값을 저장할 때, 체크 해제(=false, 0)로 명확히 저장하려면 폼에서 항상 값이 전달되어야 함

## 3. checkbox만 사용할 때의 문제점
- 체크 해제 시 name/value가 아예 전송되지 않으므로, 서버에서는 해당 필드가 **존재하지 않는 것**으로 인식
- 기존 DB 값이 true(1)이었다면, 체크 해제 후에도 값이 그대로 남아 **false(0)로 갱신되지 않는 버그**가 발생
- 예를 들어, 회원의 "광고 수신 동의"를 체크 해제해도 DB에는 여전히 1(true)로 남는 현상
- 대규모 프로젝트/관리자 페이지에서 이런 버그가 자주 발생하며, 실제로 많은 서비스에서 체크박스 해제 상태가 반영되지 않는 문제가 보고됨

## 4. 실무에서의 표준 패턴: hidden + checkbox
```html
<input type="hidden" name="foo" value="0">
<input type="checkbox" name="foo" value="1">
```
- 체크 해제: `foo=0`만 전송됨
- 체크: `foo=0&foo=1`이 전송되며, 서버에서는 마지막 값(1)이 적용됨
- **Laravel 공식 문서, Bootstrap, Tailwind UI 등에서도 권장하는 방식**

### 이 방식의 장점
- 체크 해제 시에도 항상 0이 서버로 전달되어 DB에 정확히 반영됨
- 체크박스만 쓸 때 발생하는 "해제 상태가 저장되지 않는 버그"를 원천적으로 방지
- value, id, 설명 등 반복되는 코드를 컴포넌트로 일원화하여 유지보수성 향상
- 접근성(aria, for/id)도 자동 보장

## 5. x-ui::form-checkbox 컴포넌트

### 주요 특징
- hidden input과 checkbox를 자동으로 함께 출력하여 실무 표준을 따름
- value는 기본값 1로 고정(생략 가능)
- checked는 DB 값 등으로 동적으로 제어
- id를 생략하면 name과 동일하게 자동 지정
- 설명은 slot(본문)으로 전달

### 사용 예시
```blade
<x-ui::form-checkbox
    name="can_read"
    :checked="$item->can_read"
    label="읽기"
>
    데이터를 조회할 수 있습니다.
</x-ui::form-checkbox>
```

### 렌더링 결과
```html
<input type="hidden" name="can_read" value="0">
<input id="can_read" type="checkbox" name="can_read" value="1" checked>
<label for="can_read">읽기</label>
<p id="can_read-desc">데이터를 조회할 수 있습니다.</p>
```

---

## 6. 참고
- [Laravel 공식 문서: 체크박스와 hidden input](https://laravel.com/docs/10.x/blade#checkbox-inputs)
- [MDN: Checkbox input](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/checkbox) 