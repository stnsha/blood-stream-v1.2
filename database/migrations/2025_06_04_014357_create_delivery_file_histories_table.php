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
        Schema::create('delivery_file_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_file_id')->constrained()->cascadeOnDelete();
            $table->longText('message');
            $table->string('err_code');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_file_histories');

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('delivery_files');
        Schema::enableForeignKeyConstraints();
    }
};
