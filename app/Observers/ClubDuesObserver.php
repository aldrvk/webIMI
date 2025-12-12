<?php

namespace App\Observers;

use App\Models\ClubDues;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class ClubDuesObserver
{
    /**
     * Handle the ClubDues "created" event.
     */
    public function created(ClubDues $clubDues): void
    {
        Log::create([
            'action_type' => 'INSERT',
            'table_name' => 'club_dues',
            'record_id' => $clubDues->id,
            'old_value' => null,
            'new_value' => json_encode($clubDues->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the ClubDues "updated" event.
     */
    public function updated(ClubDues $clubDues): void
    {
        Log::create([
            'action_type' => 'UPDATE',
            'table_name' => 'club_dues',
            'record_id' => $clubDues->id,
            'old_value' => json_encode($clubDues->getOriginal()),
            'new_value' => json_encode($clubDues->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the ClubDues "deleted" event.
     */
    public function deleted(ClubDues $clubDues): void
    {
        Log::create([
            'action_type' => 'DELETE',
            'table_name' => 'club_dues',
            'record_id' => $clubDues->id,
            'old_value' => json_encode($clubDues->toArray()),
            'new_value' => null,
            'user_id' => Auth::id(),
        ]);
    }
}
