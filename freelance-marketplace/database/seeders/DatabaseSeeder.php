<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /** Seed the application's database. **/
    public function run(): void
    {
        $this->call(UserRoleSeeder::class);
     
        User::create([
            'name' => 'bob_worker',
            'email' => 'bob_worker@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 1,
        ]);

        User::create([
            'name' => 'alex_customer',
            'email' => 'alex_customer@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 2,
        ]);

        User::create([
            'name' => 'john_customer',
            'email' => 'john_customer@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 2,
        ]);

        User::create([
            'name' => 'alice_admin',
            'email' => 'alice_admin@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 3,
        ]);

        User::create([
            'name' => 'escrow_service',
            'email' => 'escrow_service@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 4,
        ]);

        User::create([
            'name' => 'alex',
            'email' => 'alex@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 1,
        ]);

        User::create([
            'name' => 'ben',
            'email' => 'ben@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 1,
        ]);

        User::create([
            'name' => 'carol',
            'email' => 'carol@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 1,
        ]);

        User::create([
            'name' => 'david',
            'email' => 'david@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 1,
        ]);

        User::create([
            'name' => 'emma',
            'email' => 'emma@example.com',
            'password' => bcrypt('password'),
            'user_role_id' => 1,
        ]);

        $this->call(UserAvatarSeeder::class);
        $this->call(BankAccountSeeder::class);
        $this->call(BalanceSeeder::class);
        $this->call(TransactionTypeSeeder::class);
        $this->call(TransactionSeeder::class);
        $this->call(MainOrderCategorySeeder::class);
        $this->call(SubOrderCategorySeeder::class);
        $this->call(OrderStatusSeeder::class);
        $this->call(OrderFileAttachmentSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(OrderApproveSeeder::class);
    }
}