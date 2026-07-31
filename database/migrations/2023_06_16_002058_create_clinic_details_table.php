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
        Schema::create('clinic_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('doctor_id');
            $table->string('clinic_name')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('prefix')->nullable();

            $table->string('pnumber')->nullable();
            $table->string('tnumber')->nullable();
            $table->foreign('doctor_id')->references('id')->on('users')->cascadeOnDelete();



        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinic_details');
    }
};
