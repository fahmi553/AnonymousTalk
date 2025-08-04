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
        Schema::create('comment_sentiment_logs', function (Blueprint $table) {
            $table->id('log_id');

            $table->unsignedBigInteger('comment_id');
            $table->decimal('sentiment_score', 5, 2);
            $table->string('result');
            $table->timestamps();

            $table->foreign('comment_id')->references('comment_id')->on('comments')->onDelete('cascade');
        });
    }




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_sentiment_logs');
    }
};
