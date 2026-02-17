# rqtranslate Bot Agent

## Project Overview

**rqtranslate** is a Discord bot built using the Laracord framework (v2.3+), which combines Laravel's powerful features with DiscordPHP to create a translation bot for Discord servers.

### Tech Stack
- **Language**: PHP 8.2+
- **Framework**: Laracord (Laravel + DiscordPHP)
- **Base Framework**: Laravel Zero
- **Discord Library**: DiscordPHP

## Project Structure

### Core Files
- `rqtranslate` - Main executable entry point for the bot
- `composer.json` - Dependency management and project configuration
- `box.json` - Configuration for building PHAR archives

### Key Directories
- **`app/`** - Application logic
  - `Bot.php` - Main bot class with HTTP routing
  - `Commands/` - Bot commands (e.g., RGPingCommand)
  - `Models/` - Database models (e.g., User model)
  - `Providers/` - Service providers (BotServiceProvider)
- **`config/`** - Configuration files
  - `app.php` - Application settings (name: rqtranslate)
  - `discord.php` - Discord-specific configuration
- **`database/`** - Database migrations and seeders
- **`bootstrap/`** - Application bootstrapping

## Architecture & Patterns

### Bot Configuration
- **App Name**: rqtranslate
- **Command Prefix**: `!` (configurable via DISCORD_COMMAND_PREFIX)
- **Intents**: Default + MESSAGE_CONTENT + GUILD_MEMBERS
- **HTTP Server**: Optional HTTP server on port 8080

### User Model
The User model includes:
- `username` - Discord username
- `guild_id` - Unique Discord user ID
- `is_admin` - Admin flag
- Laravel Sanctum API token support
- Helper method `getHighlightAttribute()` for Discord mentions

### Commands
Commands extend `Laracord\Commands\Command` and include:
- Command name and description
- `handle($message, $args)` method for execution
- `interactions()` method for button/interaction routing
- Support for embedded messages with titles, fields, and buttons

### Example: RGPingCommand
- Name: `rgping`
- Description: "Ping? Pong!"
- Shows response time
- Interactive button for resources
- Ephemeral responses for buttons

## Development Guidelines

### Creating New Commands
1. Extend `Laracord\Commands\Command`
2. Define `$name` and `$description` properties
3. Implement `handle($message, $args)` method
4. Use method chaining for message building:
   ```php
   $this->message('Text')
        ->title('Title')
        ->field('Name', 'Value')
        ->button('Label', route: 'route_name')
        ->reply($message);
   ```
5. Define interactions in `interactions()` array

### Working with Discord
- Use `Discord\Parts\*` classes for Discord entities
- Button styles: `Button::STYLE_PRIMARY`, `STYLE_SECONDARY`, etc.
- Emoji support in buttons
- Ephemeral messages via `reply($interaction, ephemeral: true)`

### HTTP Routes
Define routes in `Bot.php`:
- Web routes: Use `Route::middleware('web')`
- API routes: Use `Route::middleware('api')`

### Service Providers
- Extend `Laracord\LaracordServiceProvider`
- Register services in `register()` method
- Bootstrap services in `boot()` method

## Configuration

### Environment Variables
- `APP_NAME` - Application name (default: rqtranslate)
- `APP_ENV` - Environment (default: production)
- `APP_TIMEZONE` - Timezone (default: UTC)
- `DISCORD_TOKEN` - Discord bot token (required)
- `DISCORD_BOT_DESCRIPTION` - Bot description
- `DISCORD_COMMAND_PREFIX` - Command prefix (default: !)
- `HTTP_SERVER` - HTTP server address (default: :8080)

### Admin Access
Bot admins are configured in `config/discord.php`:
```php
'admins' => [
    '282246894787493889' // Discord user IDs
],
```

## Features & Capabilities

### Laracord Features Used
- ✅ Command generation and handling
- ✅ Event listeners
- ✅ Slash commands support
- ✅ Interaction routing (buttons, actions)
- ✅ HTTP server with Laravel routing
- ✅ Database support via Laravel
- ✅ Service/task management
- ✅ Beautiful console logging with timestamps

### Current Commands
- **rgping** - Basic ping/pong command with interactive resources button

## Best Practices

### Code Style
- Use PHP 8.2+ features
- Follow PSR-4 autoloading
- Maintain Laravel coding standards
- Use Laravel Pint for code formatting

### Bot Development
1. Always validate Discord tokens in .env
2. Use ephemeral messages for user-specific responses
3. Implement interaction routes for buttons
4. Keep command logic focused and modular
5. Use database models for persistent data
6. Log important events to console

### Security
- Keep DISCORD_TOKEN private
- Validate admin permissions for sensitive commands
- Use environment variables for secrets
- Never commit `.env` files

## Testing & Debugging
- Use Laravel's built-in testing features
- Console timestamps format: `h:i:s A`
- Check logs for Discord connection issues
- Verify intents are enabled in Discord Developer Portal

## Resources

### Documentation
- **Laracord**: https://laracord.com
- **Laravel**: https://laravel.com
- **DiscordPHP**: https://discord-php.github.io/DiscordPHP
- **Discord API**: https://discord.com/developers/docs

### Repository
- **GitHub**: https://github.com/chrRtg/rqtranslate
- **Laracord Framework**: https://github.com/laracord/framework

## Common Tasks

### Adding a Translation Feature
When implementing translation functionality:
1. Create a new command in `app/Commands/`
2. Integrate translation API (e.g., Google Translate, DeepL)
3. Handle language detection and selection
4. Store user preferences in database
5. Use Discord embeds for formatted responses

### Working with Database
1. Create migrations in `database/migrations/`
2. Define models in `app/Models/`
3. Use Eloquent ORM for queries
4. Configure database in `.env`

### Deploying Bot
1. Set environment to production
2. Configure all environment variables
3. Run `composer install --no-dev`
4. Use `php rqtranslate` to start bot
5. Consider using process managers (PM2, Supervisor)

## Notes for AI Assistants

When helping with this codebase:
- This is a **Discord bot**, not a web application
- Commands are chat commands, not CLI commands
- Focus on Discord interactions and bot functionality
- Translation features appear to be the main goal
- Database is configured but implementation may be minimal
- HTTP server is optional and secondary to bot functionality
- Always respect Discord API rate limits and best practices
