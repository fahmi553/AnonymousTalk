<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(Auth::user()->unreadNotifications);
    }

    public function markAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Marked as read']);
    }

    public function destroy($id)
    {
        Auth::user()->notifications()->where('id', $id)->first()->delete();
        return response()->json(['message' => 'Notification dismissed']);
    }
}
