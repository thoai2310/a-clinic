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
        Schema::create('auto_tag_rule_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('form_id')->unsigned();
            $table->bigInteger('tag_id')->unsigned();
            $table->string('logic_operator')->default('AND')->comment('AND OR');
            $table->integer('status')->default(1)->comment('-1: inactive, 1: active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_tag_rule_groups');
    }
};
