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
        Schema::create('answer_customer', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('form_customer_id')->unsigned();
            $table->bigInteger('form_question_id')->unsigned();
            $table->bigInteger('question_option_id')->unsigned()->nullable();
            $table->text('answer_text')->nullable()->comment('for others options');

            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_customer');
    }
};
