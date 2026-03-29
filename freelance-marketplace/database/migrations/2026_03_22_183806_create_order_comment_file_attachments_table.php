<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     **/
    public function up(): void
    {
        Schema::create('order_comment_file_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('stored_filename');
            $table->string('original_filename');
            $table->foreignId('order_comment_id')->constrained('order_comments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     **/
    public function down(): void
    {
        Schema::dropIfExists('order_comment_file_attachments');
    }
};
