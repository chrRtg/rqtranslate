#!/bin/bash

# --- CONFIGURATION ---
# The absolute path to your bot's directory
BOT_DIR="/home/www/discordbots/rqtranslate"

# The name/location of your PID file
PID_FILE="$BOT_DIR/bot.pid"
# ---------------------

# 1. Check if the PID file even exists
if [ ! -f "$PID_FILE" ]; then
    echo "❓ Cannot stop: PID file not found. Is the bot already offline?"
    exit 1
fi

# 2. Get the PID
PID=$(cat "$PID_FILE")

# 3. Try to stop the process
echo "Stopping bot (PID: $PID)..."
if kill "$PID" 2>/dev/null; then
    # Give it a few seconds to shut down gracefully
    sleep 2

    # Force clean up the PID file if the bot didn't delete it
    if [ -f "$PID_FILE" ]; then
        rm "$PID_FILE"
    fi

    echo "✅ Bot stopped successfully."
else
    echo "❌ Failed to stop process $PID. It may have already crashed or you lack permissions."
    # Clean up the stale file anyway so you can restart fresh
    rm "$PID_FILE"
fi
