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
        Schema::create('test_result_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('panel_item_id')->constrained()->cascadeOnDelete();
            $table->string('value')->nullable();
            $table->string('flag')->nullable();
            $table->longText('test_notes')->nullable();
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
        Schema::dropIfExists('test_result_items');

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('test_results');
        Schema::dropIfExists('panel_items');
        Schema::enableForeignKeyConstraints();
    }
};
