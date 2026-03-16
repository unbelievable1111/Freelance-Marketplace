<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = DB::table('users')->get();

        foreach ($users as $user) 
        {
            DB::table('balances')->insert([ 'user_id' => $user->id, 'amount' => 0, 'escrowed_amount' => 0, 'updated_at' => now(), 'created_at' => now() ]);
        }
    }
}
