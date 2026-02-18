<?php

namespace App\Support;

class LoggingSanityCheck
{
    /**
     * Inspect logging configuration and target writability.
     *
     * @return array{ok:bool,default:string|null,issues:array<int,string>,checked_channels:array<int,string>}
     */
    public function inspect(): array
    {
        $issues = [];
        $checkedChannels = [];

        $defaultChannel = config('logging.default');
        $channels = config('logging.channels', []);

        if (! is_array($channels) || empty($channels)) {
            $issues[] = 'No logging channels are configured.';
        }

        if (! is_string($defaultChannel) || $defaultChannel === '') {
            $issues[] = 'No default logging channel is configured (`logging.default`).';
        } elseif (is_array($channels) && ! array_key_exists($defaultChannel, $channels)) {
            $issues[] = "Default logging channel [{$defaultChannel}] is not defined in logging.channels.";
        } else {
            $this->validateChannel($defaultChannel, $channels, $issues, $checkedChannels);
        }

        return [
            'ok' => empty($issues),
            'default' => $defaultChannel,
            'issues' => $issues,
            'checked_channels' => array_values(array_unique($checkedChannels)),
        ];
    }

    /**
     * @param array<string,mixed> $channels
     * @param array<int,string> $issues
     * @param array<int,string> $checkedChannels
     */
    protected function validateChannel(string $channelName, array $channels, array &$issues, array &$checkedChannels): void
    {
        if (in_array($channelName, $checkedChannels, true)) {
            return;
        }

        $checkedChannels[] = $channelName;

        if (! array_key_exists($channelName, $channels) || ! is_array($channels[$channelName])) {
            $issues[] = "Logging channel [{$channelName}] is missing or invalid.";
            return;
        }

        $channelConfig = $channels[$channelName];
        $driver = $channelConfig['driver'] ?? null;

        if (! is_string($driver) || $driver === '') {
            $issues[] = "Logging channel [{$channelName}] has no valid driver configured.";
            return;
        }

        if ($driver === 'stack') {
            $stackChannels = $channelConfig['channels'] ?? [];
            if (is_string($stackChannels)) {
                $stackChannels = array_values(array_filter(array_map('trim', explode(',', $stackChannels))));
            }

            if (! is_array($stackChannels) || empty($stackChannels)) {
                $issues[] = "Stack channel [{$channelName}] has no child channels configured.";
                return;
            }

            foreach ($stackChannels as $childChannel) {
                if (! is_string($childChannel) || $childChannel === '') {
                    $issues[] = "Stack channel [{$channelName}] contains an invalid child channel entry.";
                    continue;
                }

                $this->validateChannel($childChannel, $channels, $issues, $checkedChannels);
            }

            return;
        }

        if (in_array($driver, ['single', 'daily'], true)) {
            $path = $channelConfig['path'] ?? null;

            if (! is_string($path) || $path === '') {
                $issues[] = "{$driver} channel [{$channelName}] has no log file path configured.";
                return;
            }

            $this->validateWritablePath($path, $channelName, $issues);
            return;
        }

        if ($driver === 'stderr') {
            $stream = $channelConfig['with']['stream'] ?? 'php://stderr';
            if (is_string($stream) && ! str_starts_with($stream, 'php://')) {
                $this->validateWritablePath($stream, $channelName, $issues);
            }
        }
    }

    /**
     * @param array<int,string> $issues
     */
    protected function validateWritablePath(string $path, string $channelName, array &$issues): void
    {
        $directory = dirname($path);

        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            $issues[] = "Channel [{$channelName}] log directory is missing and could not be created: {$directory}";
            return;
        }

        if (! is_writable($directory)) {
            $issues[] = "Channel [{$channelName}] log directory is not writable: {$directory}";
            return;
        }

        if (file_exists($path) && ! is_writable($path)) {
            $issues[] = "Channel [{$channelName}] log file exists but is not writable: {$path}";
            return;
        }

        if (! file_exists($path)) {
            $created = @file_put_contents($path, '', FILE_APPEND);
            if ($created === false) {
                $issues[] = "Channel [{$channelName}] log file could not be created: {$path}";
            }
        }
    }
}
