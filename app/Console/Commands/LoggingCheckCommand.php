<?php

namespace App\Console\Commands;

use App\Support\LoggingSanityCheck;
use LaravelZero\Framework\Commands\Command;

class LoggingCheckCommand extends Command
{
    /**
     * The signature of the command.
     */
    protected $signature = 'logging:check';

    /**
     * The console command description.
     */
    protected $description = 'Validate logging configuration and log-target writability.';

    /**
     * Execute the console command.
     */
    public function handle(LoggingSanityCheck $check): int
    {
        $result = $check->inspect();

        if ($result['ok']) {
            $checked = implode(', ', $result['checked_channels']);
            $this->info("Logging check passed. Default channel: {$result['default']}.");

            if ($checked !== '') {
                $this->line("Checked channels: {$checked}");
            }

            return self::SUCCESS;
        }

        $this->error('Logging check failed with the following issues:');
        foreach ($result['issues'] as $issue) {
            $this->line("- {$issue}");
        }

        return self::FAILURE;
    }
}
