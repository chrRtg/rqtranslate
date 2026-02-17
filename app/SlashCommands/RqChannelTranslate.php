<?php

namespace App\SlashCommands;

use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Interactions\Command\Option;
use App\Models\ChannelTranslate;
use App\Traits\CheckServerPermission;
use Laracord\Commands\SlashCommand;

class RqChannelTranslate extends SlashCommand
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'rq-channel-translate';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable automatic translation for the current channel.';

    /**
     * The command options.
     *
     * @var array
     */
    protected $options = [
        [
            'name' => 'status',
            'description' => 'View the current translation status for this channel.',
            'type' => Option::SUB_COMMAND,
        ],
        [
            'name' => 'off',
            'description' => 'Disable any translation for this channel.',
            'type' => Option::SUB_COMMAND,
        ],
        [
            'name' => 'on',
            'description' => 'Enable translation by flag emoji in this channel.',
            'type' => Option::SUB_COMMAND,
        ],
        [
            'name' => 'auto',
            'description' => 'Enable autotranslate and select the channel the translation is sent to.',
            'type' => Option::SUB_COMMAND,
            'options' => [
                [
                    'name' => 'channel',
                    'description' => 'The channel to enable autotranslate to.',
                    'type' => Option::CHANNEL,
                    'required' => true,
                ],
                [
                    'name' => 'language',
                    'description' => 'The target language.',
                    'type' => Option::STRING,
                    'required' => true,
                    'choices' => [
                        ['name' => '🇬🇧 English (English)', 'value' => 'en-us'],
                        ['name' => '🇫🇷 Français (French)', 'value' => 'fr'],
                        ['name' => '🇪🇸 Español (Spanish)', 'value' => 'es'],
                        ['name' => '🇵🇱 Polski (Polish)', 'value' => 'pl'],
                        ['name' => '🇮🇹 Italiano (Italian)', 'value' => 'it'],
                        ['name' => '🇺🇦 Українська (Ukrainian)', 'value' => 'ua'],
                        ['name' => '🇩🇪 Deutsch (German)', 'value' => 'de'],
                    ],
                ],
            ],
        ],
    ];

    /**
     * The permissions required to use the command.
     *
     * @var array
     */
    protected $permissions = [];

    /**
     * Indicates whether the command requires admin permissions.
     *
     * @var bool
     */
    protected $admin = false;

    /**
     * Indicates whether the command should be displayed in the commands list.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * supported language codes for translation
     * @var array
     */
    protected $supported_languages = ['EN-US', 'FR', 'ES', 'PL', 'IT', 'UA', 'DE'];

    /**
     * Handle the slash command.
     *
     * @param  \Discord\Parts\Interactions\Interaction  $interaction
     * @return mixed
     */

    use CheckServerPermission;

    /**
     * Handle the command.
     * Enable or disable automatic translation for the current channel based on the provided subcommand and options.
     * - If the "status" subcommand is used, reply with the current autotranslate status for this channel.
     * - If the "off" subcommand is used, disable autotranslate for this channel and reply with a confirmation message.
     * - If the "on" subcommand is used, enable autotranslate for this channel to the specified target channel and language, and reply with a confirmation message.
     * @param mixed $interaction
     * @return \React\Promise\PromiseInterface
     */
    public function handle($interaction)
    {

        if (!$this->isDiscordAllowed($interaction)) {
            return null;
        }

        // check in Table ChannelTranslate if there is an entry for the current channel_id and for the current diccord Server (guild_id),
        $channelTranslate = ChannelTranslate::where('channel_id', $interaction->channel_id)
            ->where('guild_id', $interaction->guild_id)
            ->first();

        if (array_key_exists('status', $this->value())) {
            /**
             *
             * handle the "status" subcommand, reply with the current autotranslate status for this channel
             *
             */
            if ($channelTranslate) {
                // get the name of the target channel and the target language from the database and include it in the response message
                // and create a meaningful message like "Automatic translation to #general in DE is currently enabled for this channel."
                $channel = $this->discord->getChannel($channelTranslate->target_channel_id);
                if (!$channel) {
                    return $this->message('Target channel not found.')->reply($interaction, ephemeral: true);
                }
                $to_channel_name = $channel->name;
                $to_language = $channelTranslate->target_language;

                // check if autotranslate is enabled for this channel,
                if ($channelTranslate->autotranslate) {
                    return $this
                        ->message('Automatic translation to #' . $to_channel_name . ' in ' . $to_language . ' is currently enabled for this channel.')
                        ->reply($interaction, ephemeral: true);
                } else {
                    return $this
                        ->message('Translation by emoji flag reaction is currently enabled for this channel.')
                        ->reply($interaction, ephemeral: true);
                }

            } else {
                // no autotranslation entry for this channel, reply with a message like "Automatic translation is disabled for this channel."
                return $this
                    ->message('Translation is currently disabled for this channel.')
                    ->reply($interaction, ephemeral: true);
            }
        } else if (array_key_exists('off', $this->value())) {
            /**
             *
             * handle the "off" subcommand, disable autotranslate for this channel and reply with a confirmation message
             *
             */

            // remove the entry from the database
            ChannelTranslate::where('channel_id', $interaction->channel_id)
                ->where('guild_id', $interaction->guild_id)
                ->delete();

            return $this
                ->message('Automatic translation is now disabled for this channel.')
                ->reply($interaction, ephemeral: true);
        } else if (array_key_exists('on', $this->value())) {
            /**
             *
             * handle the "on" subcommand, enable autotranslate for this channel and reply with a confirmation message
             *
             */

            $this->enableChannelTranslation($interaction->guild_id, $interaction->channel_id);

            return $this
                ->message('Translation by emoji flag reaction is now enabled for this channel.')
                ->reply($interaction, ephemeral: true);
        } else if (array_key_exists('auto', $this->value()) && $this->value('auto.channel')) {
            /**
             *
             * enable autotranslate for this channel to the specified target channel and language, and reply with a confirmation message.
             *
             */

            $target_language = strtoupper($this->value('auto.language'));
            if (!in_array($target_language, $this->supported_languages)) {
                return $this
                    ->message('Unsupported language. Supported languages are: ' . implode(', ', $this->supported_languages))
                    ->reply($interaction, ephemeral: true);
            }

            // add entry to the database with the channel_id, guild_id and target_channel_id
            $this->enableAutomaticChannelTranslation($interaction->guild_id, $interaction->channel_id, $this->value('auto.channel'), $target_language);

            // respond with a confirmation message including the name of the target channel and the target language
            $to_channel_name = $this->discord->getChannel($this->value('auto.channel'))->name;
            return $this
                ->message('Automatic translation to #' . $to_channel_name . ' in ' . $target_language . ' is now enabled for this channel.')
                ->reply($interaction, ephemeral: true);
        }

        // fallback if no valid subcommand or options were provided
        return $this
            ->message('Invalid subcommand or missing channel option. Please use /rq-channel-autotranslate with a valid subcommand and options.')
            ->reply($interaction);
    }

    /**
     * Enable automatic channel translation from one channel to another.
     * if there is already a record for the same guild_id and channel_id, update the target_channel_id
     * @param string $guild_id
     * @param string $source_channel_id
     * @param string $target_channel_id
     * @return ChannelTranslate
     */
    private function enableAutomaticChannelTranslation(string $guild_id, string $source_channel_id, string $target_channel_id, string $target_language = 'DE'): ChannelTranslate
    {
        // create a record in the database with the guild_id, channel_id and target_channel_id, if there is already a record for the same guild_id and channel_id, update the target_channel_id
        return ChannelTranslate::updateOrCreate(
            ['guild_id' => $guild_id, 'channel_id' => $source_channel_id],
            ['target_channel_id' => $target_channel_id, 'target_language' => $target_language, 'autotranslate' => true]
        );
    }

    private function enableChannelTranslation(string $guild_id, string $source_channel_id): ChannelTranslate
    {
        return ChannelTranslate::updateOrCreate(
            ['guild_id' => $guild_id, 'channel_id' => $source_channel_id],
            ['target_channel_id' => $source_channel_id, 'target_language' => 'DE', 'autotranslate' => false]
        );
    }
}
