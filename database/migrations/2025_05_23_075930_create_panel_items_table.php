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
        Schema::create('panel_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('panel_id');
            $table->string('name');
            $table->string('decimal_point')->nullable();
            $table->string('ref_range')->nullable();
            $table->string('unit')->nullable();
            $table->string('ordinal_id')->nullable();
            $table->string('type')->nullable();
            $table->string('identifier')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('panel_id')->references('id')->on('panels');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panel_items');
    }
};
