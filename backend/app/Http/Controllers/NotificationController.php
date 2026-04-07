<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(AppNotification::latest()->limit(50)->get());
    }

    public function unreadCount()
    {
        return response()->json(['count' => AppNotification::whereNull('read_at')->count()]);
    }

    public function markRead($id)
    {
        AppNotification::where('id', $id)->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function markAllRead()
    {
        AppNotification::whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
