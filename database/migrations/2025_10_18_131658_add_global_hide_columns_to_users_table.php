<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('hide_all_posts')->default(false)->after('trust_score');
            $table->boolean('hide_all_comments')->default(false)->after('hide_all_posts');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['hide_all_posts', 'hide_all_comments']);
        });
    }
};
