<?php

namespace Database\Seeders;

use App\Models\Balance;
use App\Models\Order;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    public static function makeTransaction(int $user_id, float $amount, String $operationName, int $related_user_id = null, Order $order = null): void
    {
        $user = User::findOrFail($user_id);

        $releted_user = $related_user_id ? User::findOrFail($related_user_id) : null;

        if ($operationName != 'transfer' && $operationName != 'escrow' && count($user->BankAccounts) > 0) {
            $bankAccount = $user->BankAccounts[0];
        } else {
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
        elseif ($operationName === 'escrow') {
            $balance->decrement('amount', $amount);
            $balance->increment('escrowed_amount', $amount);
            $related_balance = Balance::where('user_id', $related_user_id)->lockForUpdate()->firstOrFail();
            $related_balance->increment('amount', $amount);
        }

        $transaction_type_id = TransactionType::where('name', $operationName)->firstOrFail()->id;

        $random_time = $order ? $order->created_at : Carbon::now()->subMinutes(rand(0, 300)); 
        
        DB::table('transactions')->insert([
            'user_id'   => $user->id,
            'amount'    => $amount,
            'order_id'  => $order ? $order->id : null,
            'transaction_type_id' => $transaction_type_id , 
            'bank_account_id' => $bankAccount?->id,
            'related_user_id' => $related_user_id,
            'transfer_uuid' => (string) Str::uuid(),
            'meta' => json_encode([
                'type'        => $operationName,
                'card_number' => $bankAccount?->card_number,
                'recipient'   => $operationName === 'transfer' || $operationName === 'escrow' ? $releted_user->name : $user->name,
            ]),
            'created_at' => $random_time,
            'updated_at' => $random_time,
        ]);
    }

    /** Run the database seeds. **/
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

        $this->makeTransaction(2, 10000, 'deposit');
        $this->makeTransaction(2, 32, 'deposit');
        $this->makeTransaction(2, 20, 'withdraw');
        $this->makeTransaction(2, 20, 'transfer', 1);

        $this->makeTransaction(3, 16600, 'deposit');
        $this->makeTransaction(3, 11, 'deposit');
        $this->makeTransaction(3, 22, 'withdraw');
        $this->makeTransaction(3, 10, 'transfer', 1);
    }
}