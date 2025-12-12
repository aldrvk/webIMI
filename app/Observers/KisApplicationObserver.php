<?php

namespace App\Observers;

use App\Models\KisApplication;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class KisApplicationObserver
{
    /**
     * Handle the KisApplication "created" event.
     */
    public function created(KisApplication $kisApplication): void
    {
        Log::create([
            'action_type' => 'INSERT',
            'table_name' => 'kis_applications',
            'record_id' => $kisApplication->id,
            'old_value' => null,
            'new_value' => json_encode($kisApplication->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the KisApplication "updated" event.
     */
    public function updated(KisApplication $kisApplication): void
    {
        Log::create([
            'action_type' => 'UPDATE',
            'table_name' => 'kis_applications',
            'record_id' => $kisApplication->id,
            'old_value' => json_encode($kisApplication->getOriginal()),
            'new_value' => json_encode($kisApplication->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the KisApplication "deleted" event.
     */
    public function deleted(KisApplication $kisApplication): void
    {
        Log::create([
            'action_type' => 'DELETE',
            'table_name' => 'kis_applications',
            'record_id' => $kisApplication->id,
            'old_value' => json_encode($kisApplication->toArray()),
            'new_value' => null,
            'user_id' => Auth::id(),
        ]);
    }
}
