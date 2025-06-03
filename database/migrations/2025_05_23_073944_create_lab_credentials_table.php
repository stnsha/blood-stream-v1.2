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
        Schema::create('lab_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained()->cascadeOnDelete();
            $table->string('username')->index();
            $table->string('password');
            $table->integer('expires_at')->nullable()->index();
            $table->string('role')->nullable()->index();
            $table->boolean('is_active')->nullable()->default(true); //1 = active
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_credentials');
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('labs');
        Schema::enableForeignKeyConstraints();
    }
};
