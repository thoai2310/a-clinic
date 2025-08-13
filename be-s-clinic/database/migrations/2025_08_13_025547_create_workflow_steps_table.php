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
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('workflow_id')->unsigned();
            $table->integer('step_number')->unsigned();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('button_text')->default('Completed');
            $table->text('completion_message')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workfow_steps');
    }
};
