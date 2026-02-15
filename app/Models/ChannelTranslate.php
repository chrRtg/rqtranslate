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
        'discord_id',
        'channel_id',
    ];
}
