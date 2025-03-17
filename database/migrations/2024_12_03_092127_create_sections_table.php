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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->integer('limit');
            $table->string('teacher_id')->nullable();
            $table->foreignId('academic_class_id')->constrained('academic_classes')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            //Adding composite unique constraint on name and academic_year_id
            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
