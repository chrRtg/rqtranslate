<?php

namespace App\SlashCommands;

use App\Models\GuildRegistered;
use App\Traits\HandlesMessageDispatchErrors;
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
    use HandlesMessageDispatchErrors;

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
            'name' => 'status',
            'description' => 'View the current registration status and usage for this server.',
            'type' => Option::SUB_COMMAND,
        ],
        [
            'name' => 'usage',
            'description' => 'View the current Deepl Usage.',
            'type' => Option::SUB_COMMAND,
        ],
        [
            'name' => 'register',
            'description' => 'Register your server with RQTranslate.',
            'type' => Option::SUB_COMMAND,
            'options' => [
                [
                    'name' => 'rq-key',
                    'description' => 'Your RQTranslate registration key.',
                    'type' => Option::STRING,
                    'required' => true,
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
     * Handle the command.
     * @param mixed $interaction
     * @return \React\Promise\PromiseInterface
     */
    public function handle($interaction)
    {
        $inputToken = $this->value('register.rq-key');

        if($inputToken) {
             // If the "register" subcommand is used, attempt to register the server with the

            // Validate the input token against the one in the .env file
            if (!$inputToken || $inputToken !== env('REGISTER_TOKEN')) {
                return $this->safeMessageDispatch(
                    fn () => $this
                        ->message('Invalid registration key. Please provide a valid RQTranslate registration key to register your server.')
                        ->reply($interaction, ephemeral: true),
                    'reply',
                    ['command' => $this->name, 'subcommand' => 'register', 'guild_id' => $interaction->guild_id]
                );
            }

            // Register the guild in the database
            $this->registeredGuilds($interaction->guild_id);

            return $this->safeMessageDispatch(
                fn () => $this
                    ->message('Your server has been successfully registered with RQTranslate! You can now use translation features in this server.')
                    ->reply($interaction, ephemeral: true),
                'reply',
                ['command' => $this->name, 'subcommand' => 'register', 'guild_id' => $interaction->guild_id]
            );
        } else if ( array_key_exists('status', $this->value()) ) {
            $guild = GuildRegistered::where('guild_id', $interaction->guild_id)->first();

            if ($guild) {
                return $this->safeMessageDispatch(
                    fn () => $this
                        ->message('Your server is registered with RQTranslate. You can use translation features in this server.')
                        ->reply($interaction, ephemeral: true),
                    'reply',
                    ['command' => $this->name, 'subcommand' => 'status', 'guild_id' => $interaction->guild_id]
                );
            } else {
                return $this->safeMessageDispatch(
                    fn () => $this
                        ->message('Your server is not registered with RQTranslate. Please register your server to access translation features.')
                        ->reply($interaction, ephemeral: true),
                    'reply',
                    ['command' => $this->name, 'subcommand' => 'status', 'guild_id' => $interaction->guild_id]
                );
            }
        } else if ( array_key_exists('usage', $this->value()) ) {
            $deepl = new \App\Services\DeeplTranslate();
            $usage = $deepl->getUsage();

            $chars_used = $usage->character->count;
            $chars_limit = $usage->character->limit;
            $chars_remaining = $chars_limit - $chars_used;

            return $this->safeMessageDispatch(
                fn () => $this
                    ->message('Current Deepl Usage: ' . $chars_used . ' characters used out of ' . $chars_limit . '. You have  ' . $chars_remaining . ' characters remaining.')
                    ->reply($interaction, ephemeral: true),
                'reply',
                ['command' => $this->name, 'subcommand' => 'usage', 'guild_id' => $interaction->guild_id]
            );
        } else {
            return $this->safeMessageDispatch(
                fn () => $this
                    ->message('Invalid subcommand. Please use either "status" to check registration status or "register" to register your server with RQTranslate.')
                    ->reply($interaction, ephemeral: true),
                'reply',
                ['command' => $this->name, 'subcommand' => 'unknown', 'guild_id' => $interaction->guild_id]
            );
        }
    }

    private function registeredGuilds(string $guild_id): GuildRegistered
    {
        return GuildRegistered::firstOrCreate(
            ['guild_id' => $guild_id]
        );
    }
}
