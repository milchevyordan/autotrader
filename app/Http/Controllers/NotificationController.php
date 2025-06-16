<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CacheTag;
use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\DatabaseNotification;
use App\Services\DataTable\DataTable;
use App\Services\DataTable\RawOrdering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class NotificationController extends Controller
{
    /**
     * Show received notification via the system.
     *
     * @return Response
     */
    public function index(): Response
    {
        $user = auth()->user();

        $dataTable = (new DataTable(
            Notification::where('type', DatabaseNotification::class)
                ->where(function ($q) use ($user) {
                    $q->where('notifiable_type', User::class)
                        ->where('notifiable_id', $user->id);
                })->orWhere(function ($q) use ($user) {
                    $q->where('notifiable_type', Company::class)
                        ->where('notifiable_id', $user->company_id);
                })->orderByRaw('read_at IS NULL DESC')
        ))
            ->setColumn('notifiable_type', __('Type'), true, true)
            ->setColumn('data', __('Message'), true)
            ->setColumn('created_at', __('Date'), true)
            ->setColumn('read_at', __('Read'), true)
            ->setDateColumn('created_at', 'd.m.Y H:i')
            ->setDateColumn('read_at', 'd.m.Y H:i');

        $dataTable->setRawOrdering(new RawOrdering('read_at IS NULL'));

        return Inertia::render('notifications/Index', [
            'dataTable' => fn () => $dataTable->run(),
        ]);
    }

    /**
     * Mark notification as read.
     *
     * @param string $notificationId
     * @return RedirectResponse
     */
    public function read(string $notificationId): RedirectResponse
    {
        try {
            Notification::find($notificationId)->update(['read_at' => now()]);

            Cache::forget(auth()->id().CacheTag::User_notifications->value);

            return redirect()->back()->with('success', __('Notification marked as read.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error marking notification.')]);
        }
    }

    /**
     * Mark notification as read.
     *
     * @return RedirectResponse
     */
    public function readAll(): RedirectResponse
    {
        try {
            auth()->user()
                ->unreadNotifications
                ->markAsRead();

            Cache::forget(auth()->id().CacheTag::User_notifications->value);

            return redirect()->back()->with('success', __('All notification marked read.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error marking notifications.')]);
        }
    }
}
