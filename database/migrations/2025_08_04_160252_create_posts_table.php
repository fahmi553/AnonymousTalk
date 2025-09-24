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
        Schema::create('posts', function (Blueprint $table) {
        $table->bigIncrements('post_id');
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('category_id')->nullable();
        $table->string('title')->nullable();
        $table->text('content');
        $table->decimal('sentiment_score', 5, 2)->nullable();
        $table->enum('status', ['published', 'moderated', 'deleted'])->default('published');
        $table->timestamps();

        $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        $table->foreign('category_id')->references('category_id')->on('categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
