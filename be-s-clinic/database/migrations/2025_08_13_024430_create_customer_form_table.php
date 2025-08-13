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
        Schema::create('customer_form', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->bigInteger('form_id')->unsigned();
            $table->bigInteger('customer_id')->unsigned();
            $table->string('status')->default('new')->comment('new, not completed, completed');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_form');
    }
};
