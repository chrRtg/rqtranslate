#!/bin/bash

# --- CONFIGURATION ---
# The absolute path to your bot's directory
BOT_DIR="/home/www/discordbots/rqtranslate"

# The name/location of your PID file
PID_FILE="$BOT_DIR/bot.pid"
# ---------------------

# Check if the PID file exists
if [ ! -f "$PID_FILE" ]; then
    echo "❌ Bot is OFFLINE (PID file not found at $PID_FILE)"
    exit 1
fi

# Read the PID from the file
PID=$(cat "$PID_FILE")

# Check if the process is actually alive
if kill -0 "$PID" 2>/dev/null; then
    echo "✅ Bot is RUNNING (PID: $PID)"
    exit 0
else
    echo "⚠️  Bot is DEAD (Found PID file, but the process is gone)"
    # Optional: remove the stale PID file
    # rm "$PID_FILE"
    exit 1
fi
