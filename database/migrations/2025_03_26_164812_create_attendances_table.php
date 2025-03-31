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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->string('attendee_id');
            $table->string('attendee_type');
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->datetime('date');
            $table->string('previous_hash')->nullable();
            $table->string('hash')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['schedule_id', 'attendee_id']);
            $table->index(['attendee_id', 'attendee_type']);
            $table->index(['schedule_id','attendee_id', 'attendee_type']);

        });
    }

    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
