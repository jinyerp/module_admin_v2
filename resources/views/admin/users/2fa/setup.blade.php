@extends('jiny-admin::layouts.admin.main')

@section('title', $user->name.' 관리자 2FA 설정')
@section('description', 'Google Authenticator 앱을 설정해주세요.')

@section('content')
<div class="pt-2 pb-4">
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $user->name }} 관리자 2FA 설정</h1>
                <p class="mt-2 text-base text-gray-700">Google Authenticator 앱을 설정해주세요.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::link-light href="{{ route('admin.admin.users.show', $user->id) }}">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    취소
                </x-ui::link-light>
            </div>
        </div>
    </div>
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4">
            {{ session('error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mt-4">
            {{ session('info') }}
        </div>
    @endif
    <div class="mt-6 space-y-8">
        <x-form-section title="설정 대상 관리자" description="2FA를 설정할 관리자 정보입니다.">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                    <span class="text-gray-600 font-medium">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
            </div>
        </x-form-section>
        <x-form-section title="QR 코드 스캔" description="Google Authenticator 앱에서 QR 코드를 스캔하세요">
            <div class="flex justify-start">
                <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg">
                    <div id="qrcode" class="w-48 h-48 flex items-center justify-center">
                        <div class="text-gray-400 text-sm">QR 코드 생성 중...</div>
                    </div>
                </div>
            </div>
        </x-form-section>
        <x-form-section title="수동 입력 (선택사항)" description="QR 코드가 스캔되지 않는 경우 수동으로 시크릿 키를 입력하세요">
            <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-700">시크릿 키:</span>
                <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">{{ $secret }}</code>
                <button onclick="copySecret()" class="text-blue-600 hover:text-blue-800 text-sm">복사</button>
            </div>
        </x-form-section>
        <x-form-section title="백업 코드" description="앱 분실 시 사용할 백업 코드입니다. 안전한 곳에 보관하세요">
            <div class="grid grid-cols-2 gap-2">
                @foreach($backupCodes as $code)
                    <code class="bg-white px-2 py-1 rounded text-sm font-mono border">{{ $code }}</code>
                @endforeach
            </div>
            <div class="mt-3">
                <button onclick="copyBackupCodes()" class="text-yellow-800 hover:text-yellow-900 text-sm">백업 코드 복사</button>
            </div>
        </x-form-section>
        <x-form-section title="인증 코드 입력" description="Google Authenticator 앱에서 생성된 6자리 코드를 입력하세요">
            <form action="{{ route('admin.admin.users.2fa.enable', $user->id) }}" method="POST">
                @csrf
                <input type="hidden" name="secret" value="{{ $secret }}">
                @foreach($backupCodes as $code)
                    <input type="hidden" name="backup_codes[]" value="{{ $code }}">
                @endforeach
                
                <div class="flex items-end space-x-3">
                    <div class="flex-1">
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">인증 코드</label>
                        <input id="code" name="code" type="text" required 
                               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="000000" maxlength="6" pattern="[0-9]{6}" autocomplete="off">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">설정 완료</button>
                </div>
            </form>
        </x-form-section>
    </div>
    <div class="mt-6 bg-blue-50 rounded-lg p-4">
        <h4 class="text-sm font-medium text-blue-900 mb-2">주의사항</h4>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>• 백업 코드는 한 번만 사용할 수 있습니다</li>
            <li>• 백업 코드는 안전한 곳에 보관하세요</li>
            <li>• 앱을 분실하면 백업 코드로 복구할 수 있습니다</li>
            <li>• 설정 완료 후에는 백업 코드를 다시 확인할 수 없습니다</li>
        </ul>
    </div>
</div>

<script>
// 자동 포커스
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('code').focus();
    generateQRCode();
});

// 6자리 숫자만 입력 허용
document.getElementById('code').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
});

// QR 코드 생성
function generateQRCode() {
    const qrCodeUrl = '{{ $qrCodeUrl }}';
    const qrcodeDiv = document.getElementById('qrcode');
    
    // QR Server API 사용 (무료, 안정적)
    const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrCodeUrl)}`;
    
    const img = document.createElement('img');
    img.src = qrApiUrl;
    img.alt = 'QR Code';
    img.className = 'w-48 h-48';
    img.onload = function() {
        qrcodeDiv.innerHTML = '';
        qrcodeDiv.appendChild(img);
    };
    img.onerror = function() {
        qrcodeDiv.innerHTML = '<div class="text-red-500 text-sm">QR 코드 생성 실패<br>수동 입력을 사용하세요</div>';
    };
}

// 시크릿 키 복사
function copySecret() {
    const secret = '{{ $secret }}';
    navigator.clipboard.writeText(secret).then(function() {
        alert('시크릿 키가 복사되었습니다.');
    }).catch(function() {
        // 폴백: 텍스트 선택
        const textArea = document.createElement('textarea');
        textArea.value = secret;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('시크릿 키가 복사되었습니다.');
    });
}

// 백업 코드 복사
function copyBackupCodes() {
    const backupCodes = @json($backupCodes);
    const codesText = backupCodes.join('\n');
    
    navigator.clipboard.writeText(codesText).then(function() {
        alert('백업 코드가 복사되었습니다.');
    }).catch(function() {
        // 폴백: 텍스트 선택
        const textArea = document.createElement('textarea');
        textArea.value = codesText;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('백업 코드가 복사되었습니다.');
    });
}
</script>
@endsection 