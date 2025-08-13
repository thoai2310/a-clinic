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
        Schema::create('customer_message', function (Blueprint $table) {
            $table->id();
            $table->integer('status')->default(1)->comment('1: new');
            $table->bigInteger('customer_id');
            $table->bigInteger('message_id');
            $table->longText('message')->nullable();
            $table->dateTime('last_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_message');
    }
};
