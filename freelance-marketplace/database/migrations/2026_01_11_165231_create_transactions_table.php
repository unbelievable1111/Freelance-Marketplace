<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('transactions', function (Blueprint $table) 
        {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2)->check('amount <> 0');

            $table->foreignId('transaction_type_id')
                ->constrained('transaction_types');

            $table->foreignId('bank_account_id')
                ->nullable()
                ->constrained('bank_accounts')
                ->nullOnDelete();

            $table->foreignId('related_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->uuid('transfer_uuid')
                ->nullable()
                ->index();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /** Reverse the migrations **/
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};