<?php

namespace App\SlashCommands;

use Discord\Parts\Interactions\Interaction;
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
    protected $options = [];

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

        $interaction->respondWithMessage(
            $this
              ->message()
              ->title('Rq Channel Translate')
              ->content('Hello world!')
              ->button('👋', route: 'wave')
              ->build()
        );
    }

    /**
     * The command interaction routes.
     */
    public function interactions(): array
    {
        return [
            'wave' => fn (Interaction $interaction) => $this->message('👋')->reply($interaction),
        ];
    }
}
