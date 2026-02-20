#!/bin/bash

# call this script each minute from the  crontage like this:
# # * * * * * /home/www/discordbots/rqtranslate/runbot.sh

# Configuration
BOT_DIR="/home/www/discordbots/rqtranslate"
PID_FILE="$BOT_DIR/bot.pid"
PHP_BIN="/usr/bin/php8.4"
BOT_SCRIPT="rqtranslate"

cd "$BOT_DIR"

# Check if PID file exists
if [ -f "$PID_FILE" ]; then
    PID=$(cat "$PID_FILE")
    # Check if the process is actually running using kill -0 (doesn't kill, just checks)
    if kill -0 "$PID" > /dev/null 2>&1; then
        # Bot is running, exit safely
        exit 0
    fi
    # If we reached here, the PID file exists but the process is dead
    rm "$PID_FILE"
fi

# Start the bot and save the new PID
$PHP_BIN "$BOT_SCRIPT" > bot.log 2>&1 &
echo $! > "$PID_FILE"
