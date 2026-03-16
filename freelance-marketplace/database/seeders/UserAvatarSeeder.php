<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserAvatarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     **/
    public function run(): void
    {
        DB::table('user_avatars')->insert([
            ['path' => 'executor.png', 'user_id' => '1'],
            ['path' => 'customer.png', 'user_id' => '2'],
            ['path' => 'customer.png', 'user_id' => '3'],
            ['path' => 'admin.png', 'user_id' => '4'],
            ['path' => 'no-avatar.png', 'user_id' => '5'],
        ]);
    }
}