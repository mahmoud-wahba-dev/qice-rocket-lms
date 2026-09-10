<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToSupportConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE(local-fix): create_support_conversations already added these FKs; add only missing ones.
        $existing = collect(\Illuminate\Support\Facades\DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_conversations'"
        ))->pluck('CONSTRAINT_NAME')->all();

        Schema::table('support_conversations', function (Blueprint $table) use ($existing) {
            if (!in_array('support_conversations_support_id_foreign', $existing)) {
                $table->foreign('support_id')->on('supports')->references('id')->onDelete('cascade');
            }
            if (!in_array('support_conversations_sender_id_foreign', $existing)) {
                $table->foreign('sender_id')->on('users')->references('id')->onDelete('cascade');
            }
        });
    }
}
