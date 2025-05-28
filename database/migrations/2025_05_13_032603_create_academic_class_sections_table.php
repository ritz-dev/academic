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
        Schema::create('academic_class_sections', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('academic_year');
            $table->string('class');
            $table->string('section');

            $table->foreign('academic_year')->references('slug')->on('academic_years')->onDelete('cascade');
            $table->foreign('class')->references('slug')->on('academic_classes')->onDelete('cascade');
            $table->foreign('section')->references('slug')->on('sections')->onDelete('cascade');

            $table->unique(['academic_year', 'class', 'section']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_class_sections');
    }
};
