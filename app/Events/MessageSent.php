<?php

namespace App\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event as Events;
use Laracord\Events\Event;
use App\Traits\CheckServerPermission;
use App\Models\ChannelTranslate;
use App\Services\DiscordTools;

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

        // check if autotranslate is enabled for the channel, if so translate the message and send it to the specified channel
        $autotranslateEntry = $this->getAutotranslateEntry($message->guild_id, $message->channel_id);
        if (!$autotranslateEntry) {
            return;
        }

        // translate the message content using DeepL API
        $deepl = new \App\Services\DeeplTranslate();
        $translationResult = $deepl->translate($message->content, $autotranslateEntry->target_language);
        // $this->console()->info($translationResult->text);

        // Convert the translated HTML to Markdown for Discord
        $translated_text = $deepl->htmlToDiscordMarkdown($translationResult->text);

        //$this->console()->info($translated_text);
        //$this->console()->info('length: ' . mb_strlen($translated_text));

        $discordTools = new DiscordTools();
        $text_chunks = $discordTools->splitMarkdown($translated_text);

        // $this->console()->info('Split into ' . var_export(($text_chunks), true));

        foreach ($text_chunks as $text_chunk) {
            // send the translated message to the target channel, include a note that it was autotranslated and from which channel
            $this->safeMessageDispatch(
                fn () => $this->message('Autotranslated from #' . $discord->getChannel($message->channel_id)->name)
                    ->body($text_chunk)
                    ->send($autotranslateEntry->target_channel_id),
                'send',
                [
                    'event' => 'MESSAGE_CREATE',
                    'guild_id' => $message->guild_id,
                    'source_channel_id' => $message->channel_id,
                    'target_channel_id' => $autotranslateEntry->target_channel_id,
                ]
            );
        }
        return;
    }

    /**
     * Get the autotranslate entry for the given guild and source channel.
     *
     * @param mixed $guild_id
     * @param mixed $source_channel_id
     * @return ChannelTranslate|object|null
     */
    private function getAutotranslateEntry($guild_id, $source_channel_id)
    {
        return ChannelTranslate::where('guild_id', $guild_id)
            ->where('channel_id', $source_channel_id)
            ->where('autotranslate', true)
            ->first();
    }
}
