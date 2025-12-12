<?php

namespace App\Observers;

use App\Models\PembalapProfile;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class PembalapProfileObserver
{
    /**
     * Handle the PembalapProfile "created" event.
     */
    public function created(PembalapProfile $pembalapProfile): void
    {
        Log::create([
            'action_type' => 'INSERT',
            'table_name' => 'pembalap_profiles',
            'record_id' => $pembalapProfile->id,
            'old_value' => null,
            'new_value' => json_encode($pembalapProfile->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the PembalapProfile "updated" event.
     */
    public function updated(PembalapProfile $pembalapProfile): void
    {
        Log::create([
            'action_type' => 'UPDATE',
            'table_name' => 'pembalap_profiles',
            'record_id' => $pembalapProfile->id,
            'old_value' => json_encode($pembalapProfile->getOriginal()),
            'new_value' => json_encode($pembalapProfile->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the PembalapProfile "deleted" event.
     */
    public function deleted(PembalapProfile $pembalapProfile): void
    {
        Log::create([
            'action_type' => 'DELETE',
            'table_name' => 'pembalap_profiles',
            'record_id' => $pembalapProfile->id,
            'old_value' => json_encode($pembalapProfile->toArray()),
            'new_value' => null,
            'user_id' => Auth::id(),
        ]);
    }
}
