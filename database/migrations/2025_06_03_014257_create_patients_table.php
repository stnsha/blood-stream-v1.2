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
            $table->foreignId('doctor_code_id')->constrained()->cascadeOnDelete();
            $table->string('icno');
            $table->string('ic_type')->default('NRIC'); //IC or PN (passport number)
            $table->string('age')->nullable();
            $table->string('gender')->nullable(); //F or M
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('doctor_codes');
        Schema::enableForeignKeyConstraints();
    }
};
