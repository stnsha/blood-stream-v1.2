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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_code_id');
            $table->string('icno');
            $table->string('ic_type')->default('NRIC'); //IC or PN (passport number)
            $table->string('age')->nullable();
            $table->string('gender')->nullable(); //F or M
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('doctor_code_id')->references('id')->on('doctor_codes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
