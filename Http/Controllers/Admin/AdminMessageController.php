<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\AdminMessage;
use Jiny\Admin\Models\AdminUser;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminMessageController extends Controller
{
    /**
     * 메시지 목록
     */
    public function index(): View
    {
        $messages = AdminMessage::with('admin')->latest()->paginate(20);
        return view('admin.messages.index', compact('messages'));
    }

    /**
     * 메시지 생성 폼
     */
    public function create(): View
    {
        $admins = AdminUser::where('is_active', true)->get();
        return view('admin.messages.create', compact('admins'));
    }

    /**
     * 메시지 저장
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'admin_id' => 'required|exists:admin_emails,id',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:notice,warning,info',
        ]);
        AdminMessage::create($data);
        return redirect()->route('admin.messages.index')->with('success', '메시지가 저장되었습니다.');
    }

    // 기타 show, edit, update, destroy 등 필요시 추가
}
