<?php

namespace App\Http\Controllers;

use App\Models\Notifications;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notifications::with('schedule.coopProgram.cooperative')
            ->latest()
            ->get();

        return Inertia::render('notification/index', [
            'notifications' => $notifications,
        ]);
    }

    public function show($id)
    {
        $notification = Notifications::with('schedule.coopProgram.cooperative')
            ->findOrFail($id);

        return Inertia::render('notification/show', [
            'notification' => $notification,
        ]);
    }
}
