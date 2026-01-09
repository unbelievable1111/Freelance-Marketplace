<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) 
        {
            $table->id();
            $table->string("name", 64);
            $table->string('card_number', 16);
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->unique(['user_id', 'card_number']);
            $table->unique(['user_id', 'name']);
        });

        //Limit trigger for having not more than 5 bank accounts per one user
        DB::unprepared("
            CREATE OR REPLACE FUNCTION check_bank_accounts_limit()
            RETURNS trigger AS $$
            BEGIN
                IF (
                    SELECT COUNT(*) FROM bank_accounts WHERE user_id = NEW.user_id
                ) >= 5 THEN
                    RAISE EXCEPTION 'User cannot have more than 5 bank accounts';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER limit_bank_accounts_per_user
            BEFORE INSERT ON bank_accounts
            FOR EACH ROW
            EXECUTE FUNCTION check_bank_accounts_limit();
        ");
    }

    /** Reverse the migrations. **/
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS limit_bank_accounts_per_user ON bank_accounts;
                        DROP FUNCTION IF EXISTS check_bank_accounts_limit();');

        Schema::dropIfExists('bank_accounts');
    }
};