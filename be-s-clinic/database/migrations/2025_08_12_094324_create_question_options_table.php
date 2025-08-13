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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('question_id')->unsigned();
            $table->text('text')->comment('Text hiển thị của option (VD: "Rất hài lòng")');
            $table->text('value')->comment('Giá trị lưu trữ của option (VD: "very_satisfied")');
            $table->integer('is_other')->default(-1)->comment('-1: no 1:yes');
            $table->integer('order')->default(1)->comment('order option in a question');

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
        Schema::dropIfExists('question_options');
    }
};
