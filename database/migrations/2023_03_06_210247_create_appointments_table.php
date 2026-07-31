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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('booked_time')->nullable();
            $table->unsignedBigInteger('schedule_from');
            $table->unsignedBigInteger('booked_from')->nullable();
            $table->date('schedule_date');
            $table->integer('duration')->nullable();
            $table->integer('appointmentFees')->nullable();
            $table->string('appointment_type')->nullable();

            $table->foreign('booked_from')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('schedule_from')->references('id')->on('users')->cascadeOnDelete();
            $table->double('rate')->nullable();
            $table->string('slot_time')->nullable();
            $table->string('feedback')->nullable();
            $table->string('appointment_state')->nullable()->default('free');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
