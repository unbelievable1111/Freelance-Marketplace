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
        Schema::create('order_approves', function (Blueprint $table) 
        {
            $table->id();
            $table->foreignId('user_id')->notNullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->notNullable()->constrained('orders')->onDelete('cascade');
            $table->string('comment', 1000);
            $table->decimal('proposed_budget', 15, 2)->min(5);
            $table->integer('proposed_deadline_in_days')->min(1);
            $table->timestamps();
            $table->unique(['user_id', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_approves');
    }
};