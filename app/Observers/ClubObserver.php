<?php

namespace App\Observers;

use App\Models\Club;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class ClubObserver
{
    /**
     * Handle the Club "created" event.
     */
    public function created(Club $club): void
    {
        Log::create([
            'action_type' => 'INSERT',
            'table_name' => 'clubs',
            'record_id' => $club->id,
            'old_value' => null,
            'new_value' => json_encode($club->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Club "updated" event.
     */
    public function updated(Club $club): void
    {
        Log::create([
            'action_type' => 'UPDATE',
            'table_name' => 'clubs',
            'record_id' => $club->id,
            'old_value' => json_encode($club->getOriginal()),
            'new_value' => json_encode($club->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the Club "deleted" event.
     */
    public function deleted(Club $club): void
    {
        Log::create([
            'action_type' => 'DELETE',
            'table_name' => 'clubs',
            'record_id' => $club->id,
            'old_value' => json_encode($club->toArray()),
            'new_value' => null,
            'user_id' => Auth::id(),
        ]);
    }
}
