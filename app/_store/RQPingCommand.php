<?php

namespace App\Commands;

use App\Traits\HandlesMessageDispatchErrors;
use Discord\Builders\Components\Button;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;

class RQPingCommand extends Command
{
    use HandlesMessageDispatchErrors;

    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'rqping';

    /**
     * The command description.
     *
     * @var string|null
     */
    protected $description = 'Ping? Pong!';

    /**
     * Handle the command.
     *
     * @param  \Discord\Parts\Channel\Message  $message
     * @param  array  $args
     * @return void
     */
    public function handle($message, $args)
    {
        $this->safeMessageDispatch(
            fn () => $this
                ->message('Ping? Pong!')
                ->title('RQPing')
                ->field('Response time', $message->timestamp->diffForHumans(null, true))
                ->button('Laracord Resources', route: 'resources', emoji: '💻', style: Button::STYLE_SECONDARY)
                ->reply($message),
            'reply',
            ['command' => $this->name, 'channel_id' => $message->channel_id ?? null]
        );

        return null;
    }

    /**
     * The command interaction routes.
     */
    public function interactions(): array
    {
        return [
            'resources' => fn (Interaction $interaction) => $this->safeMessageDispatch(
                fn () => $this
                    ->message('Check out the resources below to learn more about Laracord.')
                    ->title('Laracord Resources')
                    ->buttons([
                        'Documentation' => 'https://laracord.com',
                        'GitHub' => 'https://github.com/laracord/laracord',
                    ])
                    ->reply($interaction, ephemeral: true),
                'reply',
                ['command' => $this->name, 'route' => 'resources', 'guild_id' => $interaction->guild_id]
            ),
        ];
    }
}
