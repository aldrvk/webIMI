<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        Log::create([
            'action_type' => 'INSERT',
            'table_name' => 'events',
            'record_id' => $event->id,
            'old_value' => null,
            'new_value' => json_encode($event->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        Log::create([
            'action_type' => 'UPDATE',
            'table_name' => 'events',
            'record_id' => $event->id,
            'old_value' => json_encode($event->getOriginal()),
            'new_value' => json_encode($event->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        Log::create([
            'action_type' => 'DELETE',
            'table_name' => 'events',
            'record_id' => $event->id,
            'old_value' => json_encode($event->toArray()),
            'new_value' => null,
            'user_id' => Auth::id(),
        ]);
    }
}
