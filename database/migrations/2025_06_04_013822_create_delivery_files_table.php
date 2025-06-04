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
        Schema::create('delivery_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_info_id')->constrained()->cascadeOnDelete();
            $table->string('message_control_id');
            $table->foreignId('test_result_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_files');

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('delivery_infos');
        Schema::dropIfExists('test_results');
        Schema::enableForeignKeyConstraints();
    }
};
