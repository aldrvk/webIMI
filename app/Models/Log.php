<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Satu Log dibuat oleh SATU User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get formatted action type badge
     */
    public function getActionBadgeAttribute(): string
    {
        return match($this->action_type) {
            'INSERT' => '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-200 rounded-full">INSERT</span>',
            'UPDATE' => '<span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-200 rounded-full">UPDATE</span>',
            'DELETE' => '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-200 rounded-full">DELETE</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-200 rounded-full">UNKNOWN</span>',
        };
    }

    /**
     * Get human readable table name
     */
    public function getReadableTableNameAttribute(): string
    {
        return match($this->table_name) {
            'users' => 'Users / Pengguna',
            'clubs' => 'Clubs / Klub',
            'events' => 'Events / Acara',
            'event_registrations' => 'Event Registrations / Pendaftaran Acara',
            'kis_applications' => 'KIS Applications / Aplikasi KIS',
            'club_dues' => 'Club Dues / Iuran Klub',
            'pembalap_profiles' => 'Pembalap Profiles / Profil Pembalap',
            default => ucfirst(str_replace('_', ' ', $this->table_name)),
        };
    }

    /**
     * Get changes summary
     */
    public function getChangesSummaryAttribute(): array
    {
        if ($this->action_type === 'INSERT') {
            return ['type' => 'created', 'message' => 'Record baru dibuat'];
        }

        if ($this->action_type === 'DELETE') {
            return ['type' => 'deleted', 'message' => 'Record dihapus'];
        }

        if ($this->action_type === 'UPDATE' && is_array($this->old_value) && is_array($this->new_value)) {
            $changes = [];
            foreach ($this->new_value as $key => $newVal) {
                $oldVal = $this->old_value[$key] ?? null;
                if ($oldVal != $newVal) {
                    $changes[] = [
                        'field' => $key,
                        'old' => $oldVal,
                        'new' => $newVal,
                    ];
                }
            }
            return ['type' => 'updated', 'changes' => $changes];
        }

        return ['type' => 'unknown', 'message' => 'Tidak ada perubahan'];
    }
}
