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

            $table->string('academic_year_slug');
            $table->string('class_slug');
            $table->string('section_slug');

            $table->foreign('academic_year_slug')->references('slug')->on('academic_years')->onDelete('cascade');
            $table->foreign('class_slug')->references('slug')->on('academic_classes')->onDelete('cascade');
            $table->foreign('section_slug')->references('slug')->on('sections')->onDelete('cascade');

            $table->unique(['academic_year_slug', 'class_slug', 'section_slug'],'unique_academic');
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
