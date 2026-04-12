<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->notNullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('target_id')->notNullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->notNullable()->constrained('orders')->onDelete('cascade');
            $table->tinyInteger('score')->notNullable()->check('score BETWEEN 1 AND 5');
            $table->string('feedback', 1500);
            $table->timestamps();

            $table->unique(['author_id', 'order_id']);
        });
    }

    /** Reverse the migrations. **/
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};