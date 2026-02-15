<?php

namespace App\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event as Events;
use Laracord\Events\Event;
use App\Traits\CheckServerPermission;

class MessageSent extends Event
{
    use CheckServerPermission;

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
        // also check if the server is allowed to use the bot, if not return without responding
        if ($message->author->id === env('DISCORD_APPLICATION_ID') || !$this->isDiscordAllowed($message, true)) {
            return;
        }

        $this->console()->log('The Message Create event has fired!' . $message);
        $this->console()->log('Channel ID: ' . $message->channel_id);
        $this->console()->log('Guild ID: ' . $message->guild_id);
        $this->message('thank you')->send($message->channel);

    }
}
