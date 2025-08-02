@extends('jiny-admin::layouts.admin.main')

@section('title', $user->name.' 관리자 2FA 관리')
@section('description', '2차 인증 설정을 관리합니다.')

@section('content')
<div class="pt-2 pb-4">
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $user->name }} 관리자 2FA 관리</h1>
                <p class="mt-2 text-base text-gray-700">2차 인증 설정을 관리합니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::link-light href="{{ route('admin.admin.users.show', $user->id) }}">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    돌아가기
                </x-ui::link-light>
            </div>
        </div>
    </div>
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mt-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4">
            {{ session('error') }}
        </div>
    @endif
    <div class="mt-6 space-y-8">
        <x-ui::form-section title="관리 대상" description="2FA를 관리할 관리자 정보입니다.">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                    <span class="text-gray-600 font-medium">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
            </div>
        </x-ui::form-section>
        <x-ui::form-section title="현재 상태" description="2FA 활성화 여부 및 설정 완료일">
            <div class="flex items-center mb-2">
                <svg class="h-5 w-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-green-800 font-medium">2FA가 활성화되어 있습니다</span>
            </div>
            <p class="text-sm text-green-600">
                설정 완료일: {{ $user->google_2fa_verified_at ? $user->google_2fa_verified_at->format('Y-m-d H:i:s') : '알 수 없음' }}
            </p>
        </x-ui::form-section>
        <x-ui::form-section title="백업 코드" description="앱 분실 시 사용할 백업 코드입니다. 안전한 곳에 보관하세요.">
            <div class="flex items-center mb-3">
                <span class="text-sm text-yellow-800">남은 백업 코드: <span class="font-medium">{{ $user->google_2fa_backup_codes ? count($user->google_2fa_backup_codes) : 0 }}개</span></span>
                <button onclick="regenerateBackupCodes()" class="ml-4 px-3 py-1 border border-transparent rounded-md text-sm font-medium text-yellow-800 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">백업 코드 재생성</button>
            </div>
        </x-ui::form-section>
        <x-ui::form-section title="2FA 비활성화" description="2FA를 비활성화하면 보안이 약화됩니다.">
            <div class="flex flex-col items-start">
                <p class="text-sm text-red-800 mb-3">주의: 2FA를 비활성화하면 보안이 약화됩니다.</p>
                <button onclick="confirmDisable2FA()" class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">2FA 비활성화</button>
            </div>
        </x-ui::form-section>
    </div>
</div>

<!-- 2FA 비활성화 확인 모달 -->
<div id="disableModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">2FA 비활성화 확인</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    {{ $user->name }} 관리자의 2FA를 비활성화하시겠습니까?<br>
                    이 작업은 되돌릴 수 없습니다.
                </p>
            </div>
            <div class="flex justify-center space-x-4 mt-4">
                <button onclick="closeDisableModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-400">취소</button>
                <form action="{{ route('admin.admin.users.2fa.disable', $user->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">비활성화</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// 2FA 비활성화 확인 모달
function confirmDisable2FA() {
    document.getElementById('disableModal').classList.remove('hidden');
}
function closeDisableModal() {
    document.getElementById('disableModal').classList.add('hidden');
}
// 백업 코드 재생성
function regenerateBackupCodes() {
    if (!confirm('백업 코드를 재생성하시겠습니까? 기존 백업 코드는 사용할 수 없게 됩니다.')) {
        return;
    }
    fetch('{{ route("admin.admin.users.2fa.regenerate-backup-codes", $user->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('백업 코드가 재생성되었습니다. 새로운 백업 코드를 안전한 곳에 보관하세요.');
            location.reload();
        } else {
            alert('백업 코드 재생성에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('백업 코드 재생성 중 오류가 발생했습니다.');
    });
}
document.getElementById('disableModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDisableModal();
    }
});
</script>
@endsection 