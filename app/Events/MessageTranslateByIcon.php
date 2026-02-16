<?php

namespace App\Events;

use Discord\Discord;
use Discord\Parts\WebSockets\MessageReaction;
use Discord\WebSockets\Event as Events;
use Laracord\Events\Event;
use App\Traits\CheckServerPermission;

class MessageTranslateByIcon extends Event
{
    use CheckServerPermission;

    /**
     * The event handler.
     *
     * @var string
     */
    protected $handler = Events::MESSAGE_REACTION_ADD;

    protected $flagMap = [
        // United Kingdom & Sub-divisions (DeepL: EN-GB)
        "\u{1F1EC}\u{1F1E7}" => 'EN-GB', // 🇬🇧 Union Jack
        "\u{1F3F4}\u{E0067}\u{E0062}\u{E0065}\u{E006E}\u{E0067}\u{E007F}" => 'EN-GB', // 🏴󠁧󠁢󠁥󠁮󠁧󠁿 England
        "\u{1F3F4}\u{E0067}\u{E0062}\u{E0073}\u{E0063}\u{E0074}\u{E007F}" => 'EN-GB', // 🏴󠁧󠁢󠁳󠁣󠁴󠁿 Scotland
        "\u{1F3F4}\u{E0067}\u{E0062}\u{E0077}\u{E006C}\u{E0073}\u{E007F}" => 'EN-GB', // 🏴󠁧󠁢󠁷󠁬󠁳󠁿 Wales

        // United States (DeepL: EN-US)
        "\u{1F1FA}\u{1F1F8}" => 'EN-US', // 🇺🇸

        // European Languages
        "\u{1F1EB}\u{1F1F7}" => 'FR',    // 🇫🇷 France
        "\u{1F1EA}\u{1F1F8}" => 'ES',    // 🇪🇸 Spain
        "\u{1F1F5}\u{1F1F1}" => 'PL',    // 🇵🇱 Poland
        "\u{1F1EE}\u{1F1F9}" => 'IT',    // 🇮🇹 Italy
        "\u{1F1E9}\u{1F1EA}" => 'DE',    // 🇩🇪 Germany

        // Ukraine (DeepL uses UK for Ukrainian)
        "\u{1F1FA}\u{1F1E6}" => 'UK',    // 🇺🇦 Ukraine
    ];

    /**
     * Handle the event.
     */
    public function handle(MessageReaction $reaction, Discord $discord)
    {
        // avoid responding to own bot messages to prevent infinite loops
        // dicord id of the bot is available in the .env file as DISCORD_APPLICATION_ID
        // also check if the server is allowed to use the bot, if not return without responding
        if ($reaction->user_id === env('DISCORD_APPLICATION_ID') || !$this->isDiscordAllowed($reaction, true)) {
            return;
        }

        //### tbd: Check if translation in this channel is enabled and if it a public or emphemeral response

        $target_lang = strtolower($this->flagMap[$reaction->emoji->name] ?? null);
        $emojiName = $reaction->emoji->name;

        $this->console()->log('target lang is ' . $target_lang . ' for emoji ' . $emojiName);

        if(!$target_lang) {
            return;
        }

        // Fetch the original message
        $discord->getChannel($reaction->channel_id)->messages->fetch($reaction->message_id)->then(
            function ($message) use ($discord, $reaction, $target_lang) {
                // Translate the message
                $deepl = new \App\Services\DeeplTranslate();
                $translationResult = $deepl->translate($message->content, $target_lang);

                //check if the translation result is in a other language than the original message, if not return without responding
                if ($translationResult->detectedSourceLang === $target_lang) {
                    return;
                }

                // Reply with translation
                $this->message()
                    ->body($translationResult->text)
                    ->reply($message);
            }
        );

    }
}
