<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_integrations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('connected_by')->nullable();
            $table->string('channel_id', 64)->nullable();
            $table->string('channel_title', 255)->nullable();
            $table->text('refresh_token');
            $table->text('access_token')->nullable();
            $table->unsignedInteger('access_token_expires_at')->nullable();
            $table->unsignedInteger('connected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_integrations');
    }
};
