<?php

namespace App\Events;

use Discord\Discord;
use Discord\Parts\WebSockets\MessageReaction;
use Discord\WebSockets\Event as Events;
use Illuminate\Support\Facades\Log;
use Laracord\Events\Event;

class MessageEmojiReaction extends Event
{
    /**
     * The event handler.
     *
     * @var string
     */
    protected $handler = Events::MESSAGE_REACTION_ADD;

    /**
     * Handle the event.
     */
    public function handle(MessageReaction $reaction, Discord $discord)
    {
        Log::info('The Message Reaction Add event has fired.', [
            'guild_id' => $reaction->guild_id,
            'channel_id' => $reaction->channel_id,
            'message_id' => $reaction->message_id,
            'emoji' => $reaction->emoji?->name,
            'user_id' => $reaction->user_id,
        ]);
    }
}
