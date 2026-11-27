<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'reporter_id', 'target_type', 'target_id', 'target_author_id', 'reason', 'details',
        'target_snapshot', 'status', 'handled_by', 'handled_at', 'resolution',
    ];

    protected function casts(): array
    {
        return ['handled_at' => 'datetime'];
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'insult' => 'Insulte ou propos haineux',
            'harassment' => 'Harcèlement',
            'spam' => 'Spam ou publicité',
            'spoiler' => 'Spoiler non signalé',
            'illegal' => 'Contenu illégal',
            default => 'Autre raison',
        };
    }

    public function getTargetLabelAttribute(): string
    {
        return $this->target_type === 'chat_message' ? 'Message du Salon' : 'Avis membre';
    }
}
