<?php

namespace App\SlashCommands;

use App\Traits\HandlesMessageDispatchErrors;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\SlashCommand;

class HelloCommand extends SlashCommand
{
    use HandlesMessageDispatchErrors;

    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'hello-command';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'The Hello Command slash command.';

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
    public function handle($interaction)
    {
        $interaction->respondWithMessage(
            $this
                ->message()
                ->title('Hello Command')
                ->content('Hello world!')
                ->button('👋', route: 'wave')
                ->button('😄', route: 'happy')
                ->build()
        );
    }

    /**
     * The command interaction routes.
     */
    public function interactions(): array
    {
        return [
            'wave' => fn (Interaction $interaction) => $this->safeMessageDispatch(
                fn () => $this->message('👋')->reply($interaction),
                'reply',
                ['command' => $this->name, 'route' => 'wave', 'guild_id' => $interaction->guild_id]
            ),
            'happy' => fn (Interaction $interaction) => $this->safeMessageDispatch(
                fn () => $this->message('😄')->reply($interaction),
                'reply',
                ['command' => $this->name, 'route' => 'happy', 'guild_id' => $interaction->guild_id]
            ),
        ];
    }
}
