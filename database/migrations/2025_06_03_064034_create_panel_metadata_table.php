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
        Schema::create('panel_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panel_item_id')->constrained()->cascadeOnDelete();
            $table->string('ordinal_id')->nullable();
            $table->string('type')->nullable();
            $table->string('identifier')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panel_metadata');

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('panel_items');
        Schema::enableForeignKeyConstraints();
    }
};
