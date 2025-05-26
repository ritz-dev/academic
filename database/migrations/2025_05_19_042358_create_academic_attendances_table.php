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
        Schema::create('academic_attendances', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('previous_hash')->nullable();
            $table->string('hash')->nullable();
            $table->enum('attendee_type', ['student', 'teacher']);
            $table->string('attendee_id');
            $table->foreignId('schedule_id')->constrained('daily_schedules')->onDelete('cascade');
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->datetime('date');
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['attendee_type', 'attendee_id', 'schedule_id'], 'attendee_schedule_unique');
            $table->index(['attendee_type', 'attendee_id'], 'attendee_type_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_attendances');
    }
};
