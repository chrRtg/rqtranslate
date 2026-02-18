<?php

namespace App\Menus;

use App\Traits\HandlesMessageDispatchErrors;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\User;
use Laracord\Commands\ContextMenu;

class ExampleMenu extends ContextMenu
{
    use HandlesMessageDispatchErrors;

    /**
     * The context menu name.
     *
     * @var string
     */
    protected $name = 'Example Menu';

    /**
     * The context menu type.
     */
    protected string|int $type = 'message';

    /**
     * The permissions required to use the context menu.
     *
     * @var array
     */
    protected $permissions = [];

    /**
     * Indicates whether the context menu requires admin permissions.
     *
     * @var bool
     */
    protected $admin = false;

    /**
     * Handle the context menu interaction.
     */
    public function handle(Interaction $interaction, Message|User|null $target): mixed
    {
        $interaction->respondWithMessage(
            $this
              ->message()
              ->title('Example Menu')
              ->content('Hello world!')
              ->button('👋', route: 'wave')
              ->build()
        );
    }

    /**
     * The context menu interaction routes.
     */
    public function interactions(): array
    {
        return [
            'wave' => fn (Interaction $interaction) => $this->safeMessageDispatch(
                fn () => $this->message('👋')->reply($interaction),
                'reply',
                ['menu' => $this->name, 'route' => 'wave', 'guild_id' => $interaction->guild_id]
            ),
        ];
    }
}
