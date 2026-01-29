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
        Schema::create('article_meta', function (Blueprint $table) {
            $table->uuid('article_id');
            $table->string('meta_type');
            $table->uuid('meta_id');

            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            $table->index(['article_id', 'meta_type', 'meta_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_meta');
    }
};
