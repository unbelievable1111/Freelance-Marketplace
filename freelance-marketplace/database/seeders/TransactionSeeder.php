<?php

namespace Database\Seeders;

use App\Models\Balance;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    private function makeTransaction(int $user_id, float $amount, String $operationName, int $related_user_id = null): void
    {
        $user = User::findOrFail($user_id);
        $releted_user = $related_user_id ? User::findOrFail($related_user_id) : null;

        if ($operationName != 'transfer') {
            $bankAccount = $user->BankAccounts[0];
        } 
        else 
        {
            $bankAccount = null;
        }
        
        $balance = Balance::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

        if ($operationName === 'withdraw') {
            $balance->decrement('amount', $amount);
        }
        elseif ($operationName === 'deposit') {
            $balance->increment('amount', $amount);
        }
        elseif ($operationName === 'transfer') {
            $balance->decrement('amount', $amount);
            $related_balance = Balance::where('user_id', $related_user_id)->lockForUpdate()->firstOrFail();
            $related_balance->increment('amount', $amount);
        }

        $transaction_type_id = TransactionType::where('name', $operationName)->firstOrFail()->id;

        DB::table('transactions')->insert([
            'user_id' => $user->id,
            'amount' => $amount,
            'transaction_type_id' => $transaction_type_id , 
            'bank_account_id' => $bankAccount?->id,
            'related_user_id' => $related_user_id,
            'transfer_uuid' => (string) Str::uuid(),
            'meta' => json_encode([
                'type'        => $operationName,
                'card_number' => $bankAccount?->card_number,
                'recipient'   => $operationName === 'transfer' ? $releted_user->name : $user->name,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Run the database seeds.*/
    public function run(): void {
        $this->makeTransaction(1, 50, 'deposit');
        $this->makeTransaction(1, 20, 'withdraw');
        $this->makeTransaction(1, 70, 'deposit');
        $this->makeTransaction(1, 50, 'deposit');
        $this->makeTransaction(1, 10, 'withdraw');
        $this->makeTransaction(1, 50, 'deposit');
        $this->makeTransaction(1, 50, 'deposit');
        $this->makeTransaction(1, 10, 'deposit');
        $this->makeTransaction(1, 50, 'deposit');
        $this->makeTransaction(1, 20, 'deposit');
        $this->makeTransaction(1, 50, 'deposit');
        $this->makeTransaction(1, 10, 'deposit');
        $this->makeTransaction(1, 50, 'deposit');
        $this->makeTransaction(1, 30, 'deposit');
        $this->makeTransaction(1, 20, 'deposit');
        $this->makeTransaction(1, 50, 'deposit');
        $this->makeTransaction(1, 20, 'withdraw');



        $this->makeTransaction(2, 50, 'deposit');
        $this->makeTransaction(2, 32, 'deposit');
        $this->makeTransaction(2, 20, 'withdraw');
        $this->makeTransaction(2, 20, 'transfer', 1);
    }
}