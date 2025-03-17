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
        Schema::create('academic_classes', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->integer('limit');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_classes');
    }
};
