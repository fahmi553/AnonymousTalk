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
        Schema::create('reports', function (Blueprint $table) {
            $table->id('report_id');

            $table->unsignedBigInteger('reporter_id');
            $table->unsignedBigInteger('reported_post_id');

            $table->text('reason');
            $table->enum('status', ['pending', 'reviewed', 'closed'])->default('pending');
            $table->timestamps();

            $table->foreign('reporter_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('reported_post_id')->references('post_id')->on('posts')->onDelete('cascade');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
