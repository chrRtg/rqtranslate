<?php

namespace App\SlashCommands;

use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Interactions\Command\Option;
use App\Models\ChannelTranslate;
use App\Traits\CheckServerPermission;
use Laracord\Commands\SlashCommand;

class RqChannelAutoTranslate extends SlashCommand
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'rq-channel-autotranslate';

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
            'name'        => 'status',
            'description' => 'View the current autotranslate status for this channel.',
            'type'        => Option::SUB_COMMAND,
        ],
        [
            'name'        => 'off',
            'description' => 'Disable autotranslate for this channel.',
            'type'        => Option::SUB_COMMAND,
        ],
        [
            'name'        => 'on',
            'description' => 'Enable autotranslate and select the channel the translation is sent to.',
            'type'        => Option::SUB_COMMAND,
            'options'     => [
                [
                    'name'        => 'channel',
                    'description' => 'The channel to enable autotranslate to.',
                    'type'        => Option::CHANNEL,
                    'required'    => true,
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
     * Handle the slash command.
     *
     * @param  \Discord\Parts\Interactions\Interaction  $interaction
     * @return mixed
     */

    use CheckServerPermission;

    public function handle($interaction)
    {

        if (! $this->isDiscordAllowed($interaction)) {
            return;
        }

        // check in Table ChannelTranslate if there is an entry for the current channel_id and for the current diccord Server (guild_id),
        $hasTranslate = ChannelTranslate::where('channel_id', $interaction->channel_id)
            ->where('guild_id', $interaction->guild_id)
            ->exists();

        if(array_key_exists('status', $this->value())) {
            if( $hasTranslate) {
                return $this
                    ->message('Automatic translation is currently enabled for this channel.')
                    ->reply($interaction);
            } else {
                return $this
                    ->message('Automatic translation is disabled for this channel.')
                    ->reply($interaction);
            }
        } else if(array_key_exists('off', $this->value())) {
            // remove the entry from the database
            ChannelTranslate::where('channel_id', $interaction->channel_id)
                ->where('guild_id', $interaction->guild_id)
                ->delete();

            return $this
                ->message('Automatic translation is now disabled for this channel.')
                ->reply($interaction);
        } else if(!$hasTranslate && array_key_exists('on', $this->value()) && $this->value('on.channel')) {
            // add entry to the database with the channel_id, guild_id and target_channel_id
            $this->enableAutomaticChannelTranslation($interaction->guild_id, $interaction->channel_id, $this->value('on.channel'));

            return $this
                ->message('Automatic translation is now enabled for this channel.')
                ->reply($interaction);
            ;
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
    private function enableAutomaticChannelTranslation(string $guild_id, string $source_channel_id, string $target_channel_id): ChannelTranslate
    {
        // create a record in the database with the guild_id, channel_id and target_channel_id, if there is already a record for the same guild_id and channel_id, update the target_channel_id
        return ChannelTranslate::updateOrCreate(
            ['guild_id' => $guild_id, 'channel_id' => $source_channel_id],
            ['target_channel_id' => $target_channel_id]
        );
    }

}
