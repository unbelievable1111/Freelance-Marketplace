<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_comments', function (Blueprint $table) {
            $table->id();
            $table->string('value', 1024)->unique();
            $table->foreignId('user_id')->notNullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->notNullable()->constrained('orders')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     ** Reverse the migrations.
     **/
    public function down(): void
    {
        Schema::dropIfExists('order_comments');
    }
};