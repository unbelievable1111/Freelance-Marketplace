<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transaction_types')->insert([
            ['name' => 'deposit'],
            ['name' => 'withdraw'],
            ['name' => 'transfer'],
            ['name' => 'escrow'],
            ['name' => 'refund_escrow'],
            ['name' => 'release_escrow'],
        ]);
    }
}