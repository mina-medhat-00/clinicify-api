<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_from');
            $table->unsignedBigInteger('chat_to');
            $table->integer('is_open')->nullable()->default(0);
            $table->integer('admin_restrict')->nullable()->default(0);

            $table->foreign('chat_from')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('chat_to')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chats');
    }
};
