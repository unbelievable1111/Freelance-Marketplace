<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Run the migrations **/
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) 
        {
            $table->id();
            $table->string('title', 125);
            $table->string('requirement_skills', 255);
            $table->string('short_description', 255);    
            $table->string('full_description', 5000);
            $table->decimal('budget', 15, 2)->min(0);
            $table->foreignId('customer_id')->constrained("users", 'id');
            $table->foreignId('executor_id')->nullable()->constrained("users", 'id');
            $table->foreignId('status_id')->constrained("order_statuses", 'id');
            $table->foreignId('sub_category_id')->constrained("sub_order_categories", 'id');
            $table->integer('deadline_in_days')->min(1);
            $table->dateTime('deadline_date')->nullable();
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
        });
    }

    /** Reverse the migrations **/
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });

        Schema::dropIfExists('orders');
    }
};