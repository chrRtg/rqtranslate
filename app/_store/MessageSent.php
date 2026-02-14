<?php

namespace App\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event as Events;
use Laracord\Events\Event;

class MessageSent extends Event
{
    /**
     * The event handler.
     *
     * @var string
     */
    protected $handler = Events::MESSAGE_CREATE;

    /**
     * Handle the event.
     */
    public function handle(Message $message, Discord $discord)
    {
        // avoid responding to own bot messages to prevent infinite loops
        // dicord id of the bot is available in the .env file as DISCORD_APPLICATION_ID
        if ($message->author->id === env('DISCORD_APPLICATION_ID')) {
            return;
        }

        $this->console()->log('The Message Create event has fired!' . $message);
        $this->message('thank you')->send($message->channel);

    }
}
