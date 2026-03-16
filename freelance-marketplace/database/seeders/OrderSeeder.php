<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [];

        for ($i = 1; $i <= 40; $i++) {
            $customer_id = rand(2, 3);
            $budget = rand(5, 200);

            $orders[] = [
                'title' => "Test order #$i",
                'requirement_skills' => 'PHP, Laravel, MySQL',
                'short_description' => "Short description for order $i",
                'full_description' => "Full detailed description for test order number $i. This is used for testing pagination, sorting and filtering.",
                'budget' => $budget,
                'customer_id' => $customer_id,
                'executor_id' => null,
                'status_id' => 1, // published
                'sub_category_id' => rand(1, 4),
                'deadline_in_days' => rand(1, 14),
                'deadline_date' => null,
                'created_at' => now()->subDays(rand(0, 7))->subMinutes(rand(0, 1440)),
                'updated_at' => now(),
            ];

            $escrow_service = User::where('name', 'escrow_service')->first();
        }

        foreach ($orders as $orderData) {
            $createdOrder = Order::create($orderData);
            TransactionSeeder::makeTransaction($orderData['customer_id'], $orderData['budget'], 'escrow', $escrow_service->id, $createdOrder);
        }
    }
}