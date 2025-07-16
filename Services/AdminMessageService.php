<?php

namespace Jiny\Admin\Services;

use App\Models\Admin\AdminMessage;
use Jiny\Admin\Models\AdminUser;
use Illuminate\Support\Facades\Mail;

class AdminMessageService
{
    /**
     * 메시지 목록 조회
     */
    public function getMessages(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = AdminMessage::with(['admin']);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['admin_id'])) {
            $query->where('admin_id', $filters['admin_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 20);
    }

    /**
     * 메시지 생성
     */
    public function createMessage(array $data): AdminMessage
    {
        return AdminMessage::create($data);
    }

    /**
     * 메시지 발송
     */
    public function sendMessage(AdminMessage $message): bool
    {
        $message->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);

        // 이메일 발송 로직 추가 가능
        return true;
    }

    /**
     * 전체 발송
     */
    public function sendToAll(AdminMessage $message): bool
    {
        // 전체 사용자에게 발송하는 로직
        $message->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);

        return true;
    }

    /**
     * 메시지 읽음 처리
     */
    public function markAsRead(AdminMessage $message): bool
    {
        return $message->update([
            'status' => 'read',
            'read_at' => now()
        ]);
    }
}
