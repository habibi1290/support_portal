<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }


    protected function casts(): array
    {
        return [
            'reopened_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
            ->withDefault([
                'name' => 'Gelöschtes Konto',
            ]);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
