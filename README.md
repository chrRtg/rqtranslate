# RQTranslate Discord Bot

RQTranslate is a Discord translation bot built with [Laracord](https://github.com/laracord/framework) and DeepL.

## Features

- Translation by emoji reaction (flag emoji on a message)
- Optional automatic channel translation mode
- Per-server registration gate using a registration key
- Logging via Laravel/Laravel Zero `Log` channels and levels
- Startup and manual logging sanity checks

## Bot Invite

Use this invite URL:

https://discord.com/api/oauth2/authorize?client_id=<discord_application_id>&permissions=281600&scope=bot%20applications.commands

## Local Installation

### 1) Prerequisites

- PHP 8.2+
- Composer
- A Discord application + bot token
- A DeepL API key

### 2) Install dependencies

```bash
composer install
```

### 3) Configure environment

```bash
cp .env.example .env
```

Set at least:

- `DISCORD_TOKEN`
- `DISCORD_APPLICATION_ID`
- `DEEPL_API_KEY`
- `REGISTER_TOKEN`

Generate app key:

```bash
php rqtranslate key:generate
```

### 4) Database

The database is created on the first run of the bot.

### 5) Verify logging setup (recommended)

```bash
php rqtranslate logging:check
```

### 6) Boot the bot

```bash
php rqtranslate bot:boot
```

## Production Notes

- Keep secrets only in real environment variables or private `.env` files.
- Do not commit production tokens/keys to git.
- If credentials were exposed, rotate them immediately (Discord token, DeepL key, registration key).

Suggested production logging values:

- `APP_ENV=production`
- `LOG_CHANNEL=stack`
- `LOG_STACK=single`
- `LOG_LEVEL=warning`
- `LOG_SANITY_CHECK=true`
- `LOG_SANITY_STRICT=true`

## Discord Commands

### `/rq-register-server`

Server registration and usage checks.

- `register rq-key:<key>`
  - Registers the guild if key matches `REGISTER_TOKEN`
- `status`
  - Shows whether current server is registered
- `usage`
  - Shows current DeepL usage

### `/rq-channel-translate`

Channel translation mode control.

- `status`
  - Shows current translation mode for this channel
- `on`
  - Enables translation by emoji reaction for this channel
- `off`
  - Disables translation settings for this channel
- `auto channel:<target> language:<lang>`
  - Enables automatic translation from current channel to target channel
  - Validates bot write permission for target channel before enabling

Supported language choices in command:

- `en-us`, `fr`, `es`, `pl`, `it`, `ua`, `de`

## Runtime Behavior

- Unregistered servers are blocked and receive a warning message.
- Emoji reaction translation is skipped when autotranslate is enabled for that channel.
- In `auto` mode, the bot currently translates incoming messages to German (`DE`) in the event handler.
- Message send/reply dispatch errors are caught and logged.

## Logging

Logging is configured in `config/logging.php` and controlled by env:

- `LOG_CHANNEL`
- `LOG_STACK`
- `LOG_LEVEL`

Sanity checks:

- Manual: `php rqtranslate logging:check`
- Startup: runs in `Bot::beforeBoot()` when `LOG_SANITY_CHECK=true`
- Strict fail-fast on boot when `LOG_SANITY_STRICT=true`

## Build

```bash
./rqtranslate app:build
```

---

(C) 2026 by [RtgQuack](https://discord.com/invite/qPz5JD4NWG)
