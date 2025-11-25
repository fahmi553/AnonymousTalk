<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('post_sentiment_logs', function (Blueprint $table) {
            $table->id('log_id');

            $table->unsignedBigInteger('post_id');
            $table->decimal('sentiment_score', 8, 6);
            $table->string('result');
            $table->timestamps();

            $table->foreign('post_id')->references('post_id')->on('posts')->onDelete('cascade');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_sentiment_logs');
    }
};
