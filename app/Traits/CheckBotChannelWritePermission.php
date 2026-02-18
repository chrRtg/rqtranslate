<?php

namespace App\Traits;

trait CheckBotChannelWritePermission
{
    /**
     * Check whether the bot can write into the given channel.
     */
    protected function botCanWriteToChannel(string $channelId): bool
    {
        $channel = $this->discord->getChannel($channelId);

        if (! $channel || ! method_exists($channel, 'isTextBased') || ! $channel->isTextBased()) {
            return false;
        }

        if (property_exists($channel, 'is_private') && $channel->is_private) {
            return true;
        }

        if (! method_exists($channel, 'getBotPermissions')) {
            return false;
        }

        $botPermissions = $channel->getBotPermissions();

        if (! $botPermissions) {
            return false;
        }

        return (bool) ($botPermissions->view_channel && $botPermissions->send_messages);
    }

    /**
     * Check permissions and optionally notify the interaction/message context.
     */
    protected function ensureBotCanWriteToChannel(string $channelId, $action = null, bool $notify = true): bool
    {
        if ($this->botCanWriteToChannel($channelId)) {
            return true;
        }

        if ($notify && $action) {
            $text = 'I cannot write to the selected target channel. Please grant me "View Channel" and "Send Messages" permissions there.';

            if (method_exists($this, 'safeMessageDispatch')) {
                $this->safeMessageDispatch(
                    fn () => method_exists($action, 'reply')
                        ? $action->reply($text, ephemeral: true)
                        : $action->channel->sendMessage($text),
                    'notify_missing_target_channel_permissions',
                    [
                        'guild_id' => $action->guild_id ?? null,
                        'target_channel_id' => $channelId,
                    ]
                );
            } elseif (method_exists($action, 'reply')) {
                $action->reply($text, ephemeral: true);
            } elseif (isset($action->channel) && method_exists($action->channel, 'sendMessage')) {
                $action->channel->sendMessage($text);
            }
        }

        return false;
    }
}
