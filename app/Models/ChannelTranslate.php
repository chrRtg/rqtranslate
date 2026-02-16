<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelTranslate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'guild_id',
        'channel_id',
        'target_channel_id',
        'target_language',
    ];
}
