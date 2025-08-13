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
        Schema::create('tag_rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('rule_group_id')->unsigned();
            $table->bigInteger('question_id')->unsigned()->comment('question for applying condition');
            $table->bigInteger('question_option_id')->unsigned()->nullable()
                ->comment('Option ID for questions have predefined choices (radio, checkbox).
                Null for text-based questions');
            $table->string('condition_type')->default('equals')
                ->comment('equals, contains, starts_with, ends_with, in_range');
            $table->text('condition_value')->nullable()->comment('compare value or range (JSON)');
            $table->integer('order')->default(1)->comment('Condition order');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_rule_conditions');
    }
};
