<?php

namespace App\SlashCommands;

use App\Models\GuildRegistered;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\SlashCommand;

/**
 * Slash Command to register a discord server for translation
 * The token provided by the user must match the one in the .env file as "REGISTER_TOKEN"
 * to successfully register the server. This is to prevent unauthorized registrations and
 * ensure that only servers with a valid registration key can access the translation features.
 *
 */
class RQRegisterServer extends SlashCommand
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'rq-register-server';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Register your server with RQTranslate to enable translation features.';

    /**
     * The command options.
     *
     * @var array
     */
    protected $options = [
        [
            'name' => 'rq-key',
            'description' => 'Your RQTranslate registration key.',
            'type' => Option::STRING,
            'required' => true,
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
     * Handle the command.
     * @param mixed $interaction
     * @return \React\Promise\PromiseInterface
     */
    public function handle($interaction)
    {
        $inputToken = $this->value('rq-key');

        // Validate the input token against the one in the .env file
        if(!$inputToken || $inputToken !== env('REGISTER_TOKEN')) {
            return $this
                ->message('Invalid registration key. Please provide a valid RQTranslate registration key to register your server.')
                ->reply($interaction);
        }

        // Register the guild in the database
        $this->registeredGuilds($interaction->guild_id);

        return $this
            ->message('Your server has been successfully registered with RQTranslate! You can now use translation features in this server.')
            ->reply($interaction);
    }

    private function registeredGuilds(string $guild_id): GuildRegistered
    {
        return GuildRegistered::firstOrCreate(
            ['discord_id' => $guild_id]
        );
    }
}
