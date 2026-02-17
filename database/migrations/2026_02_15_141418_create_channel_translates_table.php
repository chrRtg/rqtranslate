<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('channel_translates', function (Blueprint $table) {
            $table->id();
            $table->string('guild_id')->index();
            $table->string('channel_id')->index();
            $table->string('target_channel_id')->default(null);
            $table->string('target_language')->default('DE');
            $table->boolean('autotranslate')->default(value: false);
            $table->unique(['guild_id', 'channel_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_translates');
    }
};
