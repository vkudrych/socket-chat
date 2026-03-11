<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = 'messages';
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function received(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
