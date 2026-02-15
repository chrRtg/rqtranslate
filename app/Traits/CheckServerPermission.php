<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;

trait CheckServerPermission
{
    /**
     * Determine if the user or environment is allowed to run this action.
     */
    public function isDiscordAllowed($messageOrInteraction, $quiet = false): bool
    {
        // check if messageOrInteraction->guild_id is in database GuildRegistered as dicord_id
        if (\App\Models\GuildRegistered::where('guild_id', $messageOrInteraction->guild_id)->exists()) {
            return true;
        }

        if (!$quiet) {
            $this->notifyUnauthorized($messageOrInteraction);
        }
        return false;
    }

    protected function notifyUnauthorized($action): void
    {
        $text = "⚠️ You're server is not registered to use this.";

        method_exists($action, 'reply')
            ? $action->reply($text, ephemeral: true)
            : $action->channel->sendMessage($text);
    }
}
