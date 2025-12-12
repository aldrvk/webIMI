<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::create([
            'action_type' => 'INSERT',
            'table_name' => 'users',
            'record_id' => $user->id,
            'old_value' => null,
            'new_value' => json_encode($user->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        Log::create([
            'action_type' => 'UPDATE',
            'table_name' => 'users',
            'record_id' => $user->id,
            'old_value' => json_encode($user->getOriginal()),
            'new_value' => json_encode($user->toArray()),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Log::create([
            'action_type' => 'DELETE',
            'table_name' => 'users',
            'record_id' => $user->id,
            'old_value' => json_encode($user->toArray()),
            'new_value' => null,
            'user_id' => Auth::id(),
        ]);
    }
}
