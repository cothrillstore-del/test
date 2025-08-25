<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('review_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_comment_id')->nullable()->constrained('review_comments')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_edited')->default(false);
            $table->enum('status', ['visible', 'hidden', 'flagged'])->default('visible');
            $table->timestamps();
            
            $table->index(['review_id', 'status']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_comments');
    }
};