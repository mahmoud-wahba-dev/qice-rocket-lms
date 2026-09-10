<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('creator_id')->unsigned();
            $table->integer('amount')->unsigned()->nullable();
            $table->integer('discount')->nullable();
            $table->boolean('disabled')->default(0);
            $table->integer('created_at');

            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            // NOTE(local-fix): original file referenced teacher_id FK without creating the column;
            // Meeting model only uses creator_id, so the bogus FK is dropped for fresh installs.

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meetings');
    }
}
