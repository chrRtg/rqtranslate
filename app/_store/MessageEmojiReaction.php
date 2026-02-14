<?php

namespace App\Events;

use Discord\Discord;
use Discord\Parts\WebSockets\MessageReaction;
use Discord\WebSockets\Event as Events;
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
        $this->console()->log('The Message Reaction Add event has fired!' . var_export($reaction, true));
    }
}
