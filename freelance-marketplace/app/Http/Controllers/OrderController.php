<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\MainOrderCategory;
use App\Models\Order;
use App\Models\OrderFileAttachment;
use App\Models\OrderStatus;
use App\Models\SubOrderCategory;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index() {}

    private function validateOrderData(Request $request)
    {
        return $request->validate(
            [
                'title' =>              ['bail', 'required', 'max:125'],
                'sub_category_id' =>    ['bail', 'required', 'exists:sub_order_categories,id'],
                'requirement_skills' => ['bail', 'required', 'max:255'],
                'short_description' =>  ['bail', 'required', 'max:255'],
                'full_description' =>   ['bail', 'required', 'max:5000'],
                'budget' =>             ['bail', 'required', 'numeric', 'min:5', 'max:25000'],
                'deadline_in_days' =>   ['bail', 'required', 'integer', 'min:1'],
                'attachments' =>        ['bail', 'nullable', 'array', 'max:5'],
                'attachments.*' => [
                    'bail',
                    'file',
                    'mimes:png,jpg,jpeg,pdf,doc,docx,csv,xls,xlsx,txt',
                    'max:10240', // 10 MB per file
                ],
            ]
        );
    }

    public function createOrder(Request $request)
    {
        if (Auth::user()->UserRole->name !== 'customer') {
            return redirect()->route('home.index')->with('error', 'Only customers can create orders.');
        }

        if ($request->isMethod('get')) {
            $mainCategories = MainOrderCategory::all();
            $subCategories = SubOrderCategory::all();
            return view('components.pages.orders.create', compact('mainCategories', 'subCategories'));
        } else if ($request->isMethod('post')) {
            try {
                $data = $this->validateOrderData($request);

                DB::transaction(function () use ($data, $request) {
                    // 1. Lock the user's balance for update
                    $balance = Balance::where('user_id', Auth::id())->lockForUpdate()->first();

                    if ($data['budget'] > $balance->amount) {
                        throw new \Exception('Insufficient balance to create this order.');
                    }

                    // 2. Create the order
                    $order = Auth::user()->ordersAsCustomer()->create([
                        'title' => $data['title'],
                        'requirement_skills' => $data['requirement_skills'],
                        'short_description' => $data['short_description'],
                        'full_description' => $data['full_description'],
                        'budget' => $data['budget'],
                        'sub_category_id' => $data['sub_category_id'],
                        'status_id' => OrderStatus::where('name', 'published')->value('id'), // Assuming 1 is the ID for 'open' status
                        'deadline_in_days' => (int)$data['deadline_in_days'],
                        'customer_id' => Auth::id(),
                        'executor_id' => null,
                    ]);

                    // 3. Handle file attachments
                    if ($request->hasFile('attachments')) {
                        $attachments = $request->file('attachments');

                        foreach ($attachments as $attachment) {
                            // Here is your individual file object
                            $attachment_original_name = $attachment->getClientOriginalName();
                            $attachment_stored_name = Str::uuid() . '.' . $attachment->extension();

                            // Save attachment record in the database
                            $order->fileAttachments()->create([
                                'stored_filename' => $attachment_stored_name,
                                'original_filename' => $attachment_original_name,
                                'order_id' => $order->id,
                            ]);

                            // Process each file (e.g., store it)
                            Storage::disk('public')->putFileAs('public_order_attachments', $attachment, $attachment_stored_name);
                        }
                    }

                    //4. Deduct the budget from the user's balance and add to escrowed amount
                    $balance->decrement('amount', $data['budget']);
                    $balance->increment('escrowed_amount', $data['budget']);

                    //5. Create a transaction record for the escrow
                    Transaction::create([
                        'user_id' => Auth::id(),
                        'order_id' => $order->id,
                        'amount' => $data['budget'],
                        'transaction_type_id' =>  TransactionType::where('name', 'escrow')->value('id'),
                        'bank_account_id' => null,
                        'related_user_id' => User::where('name', 'escrow_service')->value('id'),
                        'transfer_uuid' => (string) Str::uuid(),
                        'meta' =>
                        [
                            'type'        => 'escrow',
                            'recipient'   => 'escrow_service',
                        ],
                    ]);
                });

                return redirect()->route('home.index')->with('success', 'Order created successfully!');
            } catch (\Exception $e) {

                return redirect()->back()->with('error', $e->getMessage())->withInput();
            }
        }
    }

    public function deleteAttachment(OrderFileAttachment $attachment)
    {
        $order = $attachment->order;

        if (Auth::id() !== $order->customer_id) {
            return redirect()->route('home.index')->with('error', 'You do not have permission to delete this attachment.');
        }

        if ($order->status()->first()->name !== 'published') {
            return redirect()->route('home.index')->with('error', 'Only attachments of orders in published status can be deleted.');
        }

        try {
            // Delete the file from storage
            Storage::disk('public')->delete('public_order_attachments/' . $attachment->stored_filename);

            // Delete the database record
            $attachment->delete();

            return redirect()->back()->with('success', 'Attachment deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete attachment: ' . $e->getMessage());
        }
    }

    public function editOrder(Order $order, Request $request)
    {
        if (Auth::id() !== $order->customer_id) {
            return redirect()->route('home.index')->with('error', 'You do not have permission to edit this order.');
        }

        if ($order->status()->first()->name !== 'published') {
            return redirect()->route('home.index')->with('error', 'Only orders in published status can be edited.');
        }

        if ($request->isMethod('get')) {
            $mainCategories = MainOrderCategory::all();
            $subCategories = SubOrderCategory::all();
            return view('components.pages.orders.show-order-as-editable', compact('order', 'mainCategories', 'subCategories'));
        } 
        else if ($request->isMethod('post')) 
        {
            try {
                $data = $this->validateOrderData($request);

                //TODO: Проверить бюджет
                DB::transaction(function () use ($data, $order) 
                {
                    $budgetDifference = $data['budget'] - $order->budget;
                    $escrowId = User::where('name','escrow_service')->value('id');

                    $user_balance = Balance::where('user_id', Auth::id())
                        ->lockForUpdate()
                        ->first();

                    $escrow_service_balance = Balance::where('user_id', $escrowId)
                        ->lockForUpdate()
                        ->first();

                    if ($budgetDifference > 0)
                    {
                        if ($budgetDifference > $user_balance->amount) {
                            throw new \Exception('Insufficient balance to increase the budget by this amount.');
                        }

                        $user_balance->decrement('amount', $budgetDifference);
                        $user_balance->increment('escrowed_amount', $budgetDifference);
                        $escrow_service_balance->increment('amount', $budgetDifference);

                        Transaction::create([
                            'user_id' => Auth::id(),
                            'order_id' => $order->id,
                            'amount' => $budgetDifference,
                            'transaction_type_id' =>  TransactionType::where('name', 'escrow')->value('id'),
                            'bank_account_id' => null,
                            'related_user_id' => User::where('name', 'escrow_service')->value('id'),
                            'transfer_uuid' => (string)Str::uuid(),
                            'meta' => 
                            [
                                'type'        => 'escrow',
                                'recipient'   => 'escrow_service',
                                'comment'     => 'Budget increase for order edit',
                            ],
                        ]);
                    }
                    elseif ($budgetDifference < 0) 
                    {
                        $user_balance->increment('amount', abs($budgetDifference));
                        $user_balance->decrement('escrowed_amount', abs($budgetDifference));
                        $escrow_service_balance->decrement('amount', abs($budgetDifference));

                        Transaction::create([
                            'user_id' => Auth::id(),
                            'order_id' => $order->id,
                            'amount' => abs($budgetDifference),
                            'transaction_type_id' => TransactionType::where('name', 'refund_escrow')->value('id'),
                            'bank_account_id' => null,
                            'related_user_id' => User::where('name', 'escrow_service')->value('id'),
                            'transfer_uuid' => (string)Str::uuid(),
                            'meta' =>
                            [
                                'type'        => 'refund_escrow',
                                'recipient'   => 'escrow_service',
                                'comment'     => 'Budget decrease for order edit',
                            ],
                        ]);
                    }

                    $order->update($data);
                });
                
                return redirect()->route('order.edit-order', $order)->with('success', 'Order updated successfully!');
            } 
            catch (\Exception $e) 
            {
                return redirect()->back()->with('error', $e->getMessage())->withInput();
            }
        }
    }

    private function getUniqueSubcategoriesBasedOnOrders($orders)
    {
        $uniqueSubcategoriesIDs = $orders->pluck('sub_category_id')->unique();
        $uniqueSubcategories = [];

        foreach ($uniqueSubcategoriesIDs as $subcatID) {
            $uniqueSubcategories[] = SubOrderCategory::find($subcatID);
        }

        return $uniqueSubcategories;
    }

    public function showOrder(Order $order)
    {
        $mainCategories = MainOrderCategory::all();
        $subCategories = SubOrderCategory::all();

        // Check if the authenticated user is the customer who created the order and the order is in 'published' status to allow editing
        if (Auth::id() == $order->customer_id && $order->status()->first()->name === 'published') {
            return view('components.pages.orders.show-order-as-editable', compact('order', 'mainCategories', 'subCategories'));
        } else {
            return view('components.pages.orders.show-order', compact('order'));
        }

        // Check if the authenticated user is either the customer or the freelancer associated with the order
        if ($order->status()->first()->name === 'published' || ($order->executor_id && $order->executor_id !== Auth::id())) {
            return view('components.pages.orders.show-order', compact('order'));
        }

        return redirect()->route('home.index')->with('error', 'You do not have permission to view this order.');
    }

    public function showMyOrders(Request $request)
    {
        // Get order query for customer or freelancer based on user role
        if (Auth::user()->UserRole->name === 'customer') {
            $ordersQuery = Auth::user()->ordersAsCustomer();
        }

        if (Auth::user()->UserRole->name === 'freelancer') {
            throw new \Exception('Freelancer order view is not implemented yet.');
            $ordersQuery = Auth::user()->ordersAsFreelancer();
        }

        $uniqueSubcategories = $this->getUniqueSubcategoriesBasedOnOrders($ordersQuery);
        $uniqueOrderStatuses = OrderStatus::all();

        // Apply filters based on OrderStatus
        if ($request->query('filterOrderStatus')) {
            $currentOrderStatusFilter = OrderStatus::where('id', $request->query('filterOrderStatus'))->first();

            if ($currentOrderStatusFilter) {
                $ordersQuery = $ordersQuery->where('status_id', $request->query('filterOrderStatus'));
            }
        } else {
            $currentOrderStatusFilter = (object) ['id' => 0, 'name' => 'All Statuses'];
        }

        // Apply filters based on SubOrderCategory
        if ($request->query('filterSubcategory')) {
            $currentSubcategoryFilter = SubOrderCategory::where('id', $request->query('filterSubcategory'))->first();

            if ($currentSubcategoryFilter) {
                $ordersQuery = $ordersQuery->where('sub_category_id', $request->query('filterSubcategory'));
            }
        } else {
            $currentSubcategoryFilter = (object) ['id' => 0, 'name' => 'All Subcategories'];
        }

        // Apply sorting
        switch ($request->query('sortType')) {
            case 'byTimeAsc':
                $ordersQuery = $ordersQuery->orderBy('created_at', 'asc');
                break;
            case 'byTimeDesc':
                $ordersQuery = $ordersQuery->orderBy('created_at', 'desc');
                break;
            case 'byBudgetAsc':
                $ordersQuery = $ordersQuery->orderBy('budget', 'asc');
                break;
            case 'byBudgetDesc':
                $ordersQuery = $ordersQuery->orderBy('budget', 'desc');
                break;
            case 'byStatus':
                $ordersQuery = $ordersQuery->orderBy('status_id', 'asc');
                break;
            default:
                $ordersQuery = $ordersQuery->orderBy('created_at', 'desc');
                break;
        }

        // Paginate results and maintain query parameters
        $orders = $ordersQuery->paginate(10, ['*'], 'p')->withQueryString();

        // Get total count of orders for the user (without pagination)
        $orders_total_count = Auth::user()->ordersAsCustomer()->count();

        return view('components.pages.orders.my-orders', compact(
            'orders',
            'orders_total_count',
            'uniqueSubcategories',
            'uniqueOrderStatuses',
            'currentSubcategoryFilter',
            'currentOrderStatusFilter'
        ));
    }

    public function addAttachment(Order $order, Request $request)
    {
        if (Auth::id() !== $order->customer_id) {
            return redirect()->route('home.index')->with('error', 'You do not have permission to add attachments to this order.');
        }

        if ($order->status()->first()->name !== 'published') {
            return redirect()->route('home.index')->with('error', 'Only orders in published status can be edited.');
        }

        $existingCount = $order->fileAttachments()->count();
        $maxAttachments = 5 - $existingCount;

        try {
            $request->validate([
                'attachments' => ['bail', 'array', "max:$maxAttachments"],
                'attachments.*' => [
                    'bail',
                    'file',
                    'mimes:png,jpg,jpeg,pdf,doc,docx,csv,xls,xlsx,txt',
                    'max:10240', // 10 MB per file
                ],
            ]);

            $attachments = $request->file('attachments');

            foreach ($attachments as $attachment) {
                $attachment_original_name = $attachment->getClientOriginalName();
                $attachment_stored_name = Str::uuid() . '.' . $attachment->extension();

                // Save attachment record in the database
                $order->fileAttachments()->create([
                    'stored_filename' => $attachment_stored_name,
                    'original_filename' => $attachment_original_name,
                    'order_id' => $order->id,
                ]);

                // Store the file
                Storage::disk('public')->putFileAs('public_order_attachments', $attachment, $attachment_stored_name);
            }

            return redirect()->back()->with('success', 'Attachments added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add attachments: ' . $e->getMessage());
        }
    }

    public function cancelOrder(Order $order, Request $request)
    {
        if (Auth::id() !== $order->customer_id) {
            return redirect()->route('home.index')->with('error', 'You do not have permission to cancel this order.');
        }

        if ($order->status()->first()->name !== 'published') {
            return redirect()->route('home.index')->with('error', 'Only orders in published status can be cancelled.');
        }

        try {
            DB::transaction(function () use ($order) {
                $cancelledStatusId = OrderStatus::where('name','cancelled')->value('id');
                $escrowId = User::where('name','escrow_service')->value('id');
                $refundTypeId = TransactionType::where('name','refund_escrow')->value('id');

                // 1. block the order for update
                $order = Order::lockForUpdate()->find($order->id);

                // check if order is already cancelled
                if ($order->status_id == $cancelledStatusId) {
                    throw new \Exception('Order already cancelled');
                }

                // 2. update order status to cancelled
                $order->update([
                    'status_id' => $cancelledStatusId
                ]);

                // 3. lock balances for update
                $user_balance = Balance::where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->first();

                $escrow_service_balance = Balance::where('user_id', $escrowId)
                    ->lockForUpdate()
                    ->first();

                // 4. check if escrowed amount is sufficient to cover the refund
                if ($user_balance->escrowed_amount < $order->budget) {
                    throw new \Exception('Escrow mismatch');
                }

                // 5. refund the user by moving funds from escrow back to available balance
                $user_balance->increment('amount', $order->budget);
                $user_balance->decrement('escrowed_amount', $order->budget);

                $escrow_service_balance->decrement('amount', $order->budget);

                // 6. create a transaction record for the refund
                Transaction::create([
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                    'amount' => $order->budget,
                    'transaction_type_id' => $refundTypeId,
                    'bank_account_id' => null,
                    'related_user_id' => $escrowId,
                    'transfer_uuid' => (string) Str::uuid(),
                    'meta' => [
                        'type' => 'refund_escrow',
                        'recipient' => Auth::user()->name,
                    ],
                ]);
            });

            return redirect()->back()->with('success', 'Order cancelled successfully!');
        }
        catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }
}