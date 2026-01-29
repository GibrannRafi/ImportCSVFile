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
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('article_id', 10)->nullable();
            $table->uuid('user_id');
            $table->foreignId('publisher_id')->constrained('publishers');
            $table->uuid('category_id');
            $table->string('title');
            $table->string('slug');
            $table->string('description')->nullable();
            $table->longText('content');
            $table->string('status');
            $table->boolean('is_public')->default(true);
            $table->boolean('show_ads')->default(true);
            $table->unique(['article_id', 'publisher_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
