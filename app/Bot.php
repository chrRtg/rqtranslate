<?php

namespace App;

use App\Support\LoggingSanityCheck;
use Illuminate\Support\Facades\Route;
use Laracord\Laracord;

class Bot extends Laracord
{
    /**
     * Actions to run before booting the bot.
     */
    public function beforeBoot(): void
    {
        if (! env('LOG_SANITY_CHECK', true)) {
            return;
        }

        $result = app(LoggingSanityCheck::class)->inspect();

        if ($result['ok']) {
            $this->console()->log('Logging sanity check passed for channel: '.$result['default']);
            return;
        }

        $this->console()->error('Logging sanity check failed.');
        foreach ($result['issues'] as $issue) {
            $this->console()->warn($issue);
        }

        $strict = env('LOG_SANITY_STRICT', app()->environment('production'));
        if ($strict) {
            throw new \RuntimeException('Aborting boot due to logging sanity check failure.');
        }
    }

    /**
     * The HTTP routes.
     */
    public function routes(): void
    {
        Route::middleware('web')->group(function () {
            // Route::get('/', fn () => 'Hello world!');
        });

        Route::middleware('api')->group(function () {
            // Route::get('/commands', fn () => collect($this->registeredCommands)->map(fn ($command) => [
            //     'signature' => $command->getSignature(),
            //     'description' => $command->getDescription(),
            // ]));
        });
    }
}
