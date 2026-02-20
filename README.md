# RQTranslate Discord Bot

RQTranslate is a Discord translation bot built with [Laracord](https://github.com/laracord/framework) and [DeepL API](https://github.com/DeepLcom/deepl-php).

## What it does

- Server-level access control via registration token
- Message translation by flag reaction
- Automatic channel translation mode (source channel -> target channel)
- Markdown-aware message splitting to stay under Discord message limits
- DeepL usage reporting via slash command

## Slash commands

### `/rq-register-server`

- `register rq-key:<key>`: registers current guild (must match `REGISTER_TOKEN`)
- `status`: shows whether the current guild is registered
- `usage`: shows DeepL character usage

### `/rq-channel-translate`

- `status`: show translation mode for current channel
- `on`: enable translation-by-reaction in current channel
- `off`: disable translation settings for current channel
- `auto channel:<target> language:<lang>`: enable autotranslate to target channel/language

Supported `auto` languages: `en-us`, `fr`, `es`, `pl`, `it`, `ua`, `de`

## Bot invite

Use:

```https://discord.com/api/oauth2/authorize?client_id=<discord_application_id>&permissions=281600&scope=bot%20applications.commands```

## Local setup

### 1) Prerequisites

- PHP 8.2+
- Composer
- Discord bot token + application ID
- DeepL API key

### 2) Install

```bash
composer install
cp .env.example .env
```

Set required values in `.env`:

- `DISCORD_TOKEN`
- `DISCORD_APPLICATION_ID`
- `DEEPL_API_KEY`
- `REGISTER_TOKEN`

Start bot:

```bash
php rqtranslate bot:boot
```

## Build

```bash
php rqtranslate app:build
```

## Restricted-server operation (cron scripts)

For environments without process managers, use scripts in `linux-bot/`:

- `linux-bot/runbot.sh`: ensures bot is running, starts it if down, writes `bot.pid`
- `linux-bot/checkbot.sh`: reports running/dead/offline state from `bot.pid`
- `linux-bot/stopbot.sh`: stops bot process and cleans pid file

The scripts are configured for:

- Bot directory: `/home/www/discordbots/rqtranslate`
- PHP binary: `/usr/bin/php8.4`
- Entrypoint: `rqtranslate`

Run every minute via cron on restricted hosts:

```cron
* * * * * /home/www/discordbots/rqtranslate/linux-bot/runbot.sh
```

Adjust `BOT_DIR` / `PHP_BIN` inside scripts for your server.

## Troubleshooting

- Bot does not start:
  - Check `.env` has `DISCORD_TOKEN`, `DISCORD_APPLICATION_ID`, `DEEPL_API_KEY`, `REGISTER_TOKEN`
  - Start with `php rqtranslate bot:boot` and watch output for errors

- Bot appears offline on restricted server:
  - Run `linux-bot/checkbot.sh`
  - If dead/offline, run `linux-bot/runbot.sh`
  - Ensure script paths match your host (`BOT_DIR`, `PHP_BIN`)

- `Permission denied` when running scripts:
  - Make scripts executable: `chmod +x linux-bot/*.sh`

- Bot cannot send to target channel in `auto` mode:
  - Grant bot `View Channel` and `Send Messages` permissions in the target channel

- `/rq-register-server register` fails:
  - Confirm provided key exactly matches `REGISTER_TOKEN`

- Translation fails or usage is exhausted:
  - Verify `DEEPL_API_KEY`
  - Check quota with `/rq-register-server usage`


## your Feedback

For change requests and bug reports please use the "Issues" in the [project github](https://github.com/chrRtg/rqtranslate).

In case you want to connect with the author visit my [Discord}(https://discord.gg/qPz5JD4NWG)


(C) 2026 by [RtgQuack](https://discord.com/invite/qPz5JD4NWG)
