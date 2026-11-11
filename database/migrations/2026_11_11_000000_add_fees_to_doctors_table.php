<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Doctors had no fees column so the API served salary as fees
    public function up()
    {
        Schema::table("doctors", function (Blueprint $table) {
            $table->unsignedInteger("fees")->nullable()->after("salary");
        });
    }

    public function down()
    {
        Schema::table("doctors", function (Blueprint $table) {
            $table->dropColumn("fees");
        });
    }
};
