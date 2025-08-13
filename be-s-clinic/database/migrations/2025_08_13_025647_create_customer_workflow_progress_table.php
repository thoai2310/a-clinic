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
        Schema::create('customer_workflow_progress', function (Blueprint $table) {
            $table->id();
            $table->string('status')->comment('started, in_progress', 'completed', 'paused');
            $table->bigInteger('customer_id')->unsigned();
            $table->bigInteger('workflow_id')->unsigned();
            $table->bigInteger('current_step_id')->unsigned()->nullable();
            $table->text('completed_steps')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_workflow_progress');
    }
};
