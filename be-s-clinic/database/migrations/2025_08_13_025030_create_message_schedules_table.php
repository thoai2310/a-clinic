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
        Schema::create('message_schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('message_id');

            $table->string('status')->comment('active stopped');

            $table->string('schedule_type')->comment('once, daily, weekly, monthly');
            $table->time('schedule_time');
            $table->date('schedule_date')->nullable()->comment('for once');
            $table->integer('day_of_week')->comment('0 sunday 1 monday..');
            $table->integer('day_of_month')->comment('1-31');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamp('next_run_at')->nullable();

            $table->integer('run_count')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_schedules');
    }
};
