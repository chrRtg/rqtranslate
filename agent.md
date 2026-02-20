# rqtranslate

Discord bot to translate messages by reacting with a flag emoji or autotranslating from one channel to another.

## Usage: Slash commands

### `/rq-register-server`

File: `app/SlashCommands/RQRegisterServer.php`

- `register rq-key:<key>`: register guild if key matches `REGISTER_TOKEN`
- `status`: show registration status
- `usage`: show DeepL usage

### `/rq-channel-translate`

File: `app/SlashCommands/RqChannelTranslate.php`

- `status`: show mode for current channel
- `on`: enable translation-by-reaction mode
- `off`: disable channel translation settings
- `auto channel:<target> language:<lang>`: enable autotranslate

Supported `auto` values in command choices: `en-us`, `fr`, `es`, `pl`, `it`, `ua`, `de`

## your Feedback

For change requests and bug reports please use the "Issues" in the [project github](https://github.com/chrRtg/rqtranslate).

In case you want to connect with the author visit my [Discord}(https://discord.gg/qPz5JD4NWG)

## Stack and runtime

- PHP 8.2+
- Laracord framework (`^2.3`) on Laravel Zero
- DiscordPHP via Laracord
- DeepL SDK (`deeplcom/deepl-php`)
- Markdown/HTML conversion:
  - `league/commonmark`
  - `league/html-to-markdown`

Main CLI entrypoint: `rqtranslate`

## Configuration requirements

Minimum required `.env` keys:

- `DISCORD_TOKEN`
- `DISCORD_APPLICATION_ID`
- `DEEPL_API_KEY`
- `REGISTER_TOKEN`


## server startup scripts

In case you want to run the bot 24/7 on a restricted server I provide the following script to do so.

Location: `linux-bot/`

- `runbot.sh`
  - Reads `bot.pid`
  - If PID is alive: exits
  - If stale PID: removes it
  - Starts bot in background and writes new PID
  - Logs to `bot.log`

- `checkbot.sh`
  - Reports running/dead/offline based on `bot.pid`

- `stopbot.sh`
  - Stops PID from `bot.pid`
  - Removes pid file

Current default script assumptions:

- `BOT_DIR=/home/www/discordbots/rqtranslate`
- `PHP_BIN=/usr/bin/php8.4`
- Bot binary: `rqtranslate`

Recommended cron watchdog on restricted hosts:

```cron
* * * * * /home/www/discordbots/rqtranslate/linux-bot/runbot.sh
```

## Contributor guardrails

- Prefer focused edits; avoid broad refactors.
- Keep slash-command names and option payloads backward compatible.
- Preserve message safety wrappers (`safeMessageDispatch`) around Discord I/O.
- Validate behavior in both translation modes before merging.
