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
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('ref_id')->nullable();
            $table->string('bill_code')->nullable();
            $table->string('lab_no');
            $table->dateTime('collected_date')->nullable();
            $table->dateTime('received_date')->nullable();
            $table->dateTime('reported_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_results');

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('doctor_codes');
        Schema::dropIfExists('patients');
        Schema::enableForeignKeyConstraints();
    }
};
