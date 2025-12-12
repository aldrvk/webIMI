<?php

namespace App\Observers;

use App\Models\EventRegistration;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class EventRegistrationObserver
{
    /**
     * Handle the EventRegistration "created" event.
     */
    public function created(EventRegistration $eventRegistration): void
    {
        Log::create([
            'action_type' => 'INSERT',
            'table_name' => 'event_registrations',
            'record_id' => $eventRegistration->id,
            'old_value' => null,
            'new_value' => json_encode($eventRegistration->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the EventRegistration "updated" event.
     */
    public function updated(EventRegistration $eventRegistration): void
    {
        Log::create([
            'action_type' => 'UPDATE',
            'table_name' => 'event_registrations',
            'record_id' => $eventRegistration->id,
            'old_value' => json_encode($eventRegistration->getOriginal()),
            'new_value' => json_encode($eventRegistration->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the EventRegistration "deleted" event.
     */
    public function deleted(EventRegistration $eventRegistration): void
    {
        Log::create([
            'action_type' => 'DELETE',
            'table_name' => 'event_registrations',
            'record_id' => $eventRegistration->id,
            'old_value' => json_encode($eventRegistration->toArray()),
            'new_value' => null,
            'user_id' => Auth::id(),
        ]);
    }
}
