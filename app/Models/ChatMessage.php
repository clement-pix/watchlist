<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'reply_to_id', 'message', 'moderated_by', 'moderation_reason'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function replyTo()
    {
        return $this->belongsTo(self::class, 'reply_to_id')->withTrashed();
    }

    public function reactions()
    {
        return $this->hasMany(ChatMessageReaction::class);
    }
}
