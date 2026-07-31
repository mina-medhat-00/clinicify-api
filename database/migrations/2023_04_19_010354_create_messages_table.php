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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_from');
            $table->unsignedBigInteger('message_to');
            $table->foreign('message_from')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('message_to')->references('id')->on('users')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
            $table->date('issued_date')->nullable();
            $table->timestamp('issued_time')->useCurrent();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
