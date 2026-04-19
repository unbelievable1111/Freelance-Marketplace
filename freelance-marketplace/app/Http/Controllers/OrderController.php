<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\MainOrderCategory;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\Order;
use App\Models\OrderFileAttachment;
use App\Models\OrderStatus;
use App\Models\SubOrderCategory;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    private const FEE_PERCENT = 5; // 5% (the cost of the service for each completed order)

    private function calculateFee($amount)
    {
        return $amount * self::FEE_PERCENT / 100;
    }

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
                'deadline_in_days' =>   ['bail', 'required', 'integer', 'min:1', 'max:365'],
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

    private function sortProposals(Request $request, HasMany $orderApproveQuery)
    {
        switch ($request->query('proposalSortType')) {
            case 'byTimeAsc':
                $orderApproveQuery = $orderApproveQuery->orderBy('created_at', 'asc');
                break;
            case 'byTimeDesc':
                $orderApproveQuery = $orderApproveQuery->orderBy('created_at', 'desc');
                break;
            case 'byBudgetAsc':
                $orderApproveQuery = $orderApproveQuery->orderBy('proposed_budget', 'asc');
                break;
            case 'byBudgetDesc':
                $orderApproveQuery = $orderApproveQuery->orderBy('proposed_budget', 'desc');
                break;
            case 'byDeadlineAsc':
                $orderApproveQuery = $orderApproveQuery->orderBy('proposed_deadline_in_days', 'asc');
                break;
            case 'byDeadlineDesc':
                $orderApproveQuery = $orderApproveQuery->orderBy('proposed_deadline_in_days', 'desc');
                break;
            default:
                $orderApproveQuery = $orderApproveQuery->orderBy('created_at', 'desc');
                break;
        }

        return $orderApproveQuery;
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
        } else if ($request->isMethod('post')) {
            try {
                $data = $this->validateOrderData($request);

                DB::transaction(function () use ($data, $order) {
                    $budgetDifference = $data['budget'] - $order->budget;
                    $escrowId = User::where('name', 'escrow_service')->value('id');

                    $user_balance = Balance::where('user_id', Auth::id())
                        ->lockForUpdate()
                        ->first();

                    $escrow_service_balance = Balance::where('user_id', $escrowId)
                        ->lockForUpdate()
                        ->first();

                    if ($budgetDifference > 0) {
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
                    } elseif ($budgetDifference < 0) {
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
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage())->withInput();
            }
        }
    }

    public function showOrder(Request $request, Order $order)
    {
        $order->checkDeadline();

        $mainCategories = MainOrderCategory::all();
        $subCategories = SubOrderCategory::all();
        $approves = $this->sortProposals($request, $order->orderApproves())->paginate(5, ['*'], 'p')->withQueryString();

        // Check if the authenticated user is the customer who created the order and the order is in 'published' status to allow editing
        if (Auth::id() == $order->customer_id && $order->status()->first()->name === 'published') {
            return view('components.pages.orders.show-order-as-editable', compact('order', 'mainCategories', 'subCategories', 'approves'));
        }
        else
        {
            $submittedApprove = $order->orderApproves()->where('user_id', $order->executor_id)->first();
            $myApprove = $order->orderApproves()->where('user_id', Auth::id())->first();
            $comments = $order->comments()->orderByDesc('created_at')->paginate(5, ['*'], 'p_comments')->withQueryString();

            return view('components.pages.orders.show-order', compact('order', 'myApprove', 'approves', 'submittedApprove', 'comments'));
        }

        // Check if the authenticated user is either the customer or the freelancer associated with the order
        if ($order->status()->first()->name === 'published' || ($order->executor_id && $order->executor_id !== Auth::id())) {
            return view('components.pages.orders.show-order', compact('order'));
        }

        return redirect()->back()->with('error', 'You do not have permission to view this order.');
    }

    public function showMyOrders(Request $request)
    {
        // Get order query for customer or freelancer based on user role
        if (Auth::user()->UserRole->name === 'customer') {
            $ordersQuery = Auth::user()->ordersAsCustomer();
        }

        if (Auth::user()->UserRole->name === 'executor') {
            $ordersQuery = Auth::user()->ordersAsExecutor();
        }

        $uniqueSubcategories = SubOrderCategory::getUniqueSubcategoriesFromOrders($ordersQuery);
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
        switch ($request->query('sortType')) 
        {
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

        foreach ($orders as $order) 
        {
            $order->checkDeadline();
        }

        // Get total count of orders for the user (without pagination)
        $orders_total_count = Auth::user()->UserRole->name === 'customer' ? Auth::user()->ordersAsCustomer()->count() : Auth::user()->ordersAsExecutor()->count();

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
                    'max:10240',
                ],
            ]);

            $attachments = $request->file('attachments');

            DB::beginTransaction();

            $storedFiles = [];

            foreach ($attachments as $attachment) {
                $originalName = $attachment->getClientOriginalName();
                $storedName = Str::uuid() . '.' . $attachment->extension();


                Storage::disk('public')->putFileAs(
                    'public_order_attachments',
                    $attachment,
                    $storedName
                );

                $storedFiles[] = $storedName;


                $order->fileAttachments()->create([
                    'stored_filename' => $storedName,
                    'original_filename' => $originalName,
                    'order_id' => $order->id,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Attachments added successfully!');
        } catch (\Exception $e) {

            DB::rollBack();

            if (!empty($storedFiles)) {
                foreach ($storedFiles as $file) {
                    Storage::disk('public')->delete('public_order_attachments/' . $file);
                }
            }

            return redirect()->back()->with('error', 'Failed to add attachments: ' . $e->getMessage());
        }
    }

    public function cancelOrder(Order $order, Request $request)
    {
        if (Auth::user()->UserRole->name === 'customer') 
        {
            if (Auth::id() !== $order->customer_id) 
            {
                return redirect()->back()->with('error', 'You do not have permission to cancel this order.');
            }

            if ($order->status()->first()->name !== 'published' && $order->status()->first()->name !== 'expired') 
            {
                return redirect()->back()->with('error', 'Only orders in published or expired status can be cancelled.');
            }
        }
        elseif (Auth::user()->UserRole->name === 'executor') 
        {
            if (Auth::id() !== $order->executor_id) 
            {
                return redirect()->back()->with('error', 'You do not have permission to cancel this order.');
            }

            if (!$order->isInProgress()) 
            {
                return redirect()->back()->with('error', 'Executors can cancel orders only in in_progress status.');
            }
        }
        else
        {
            return redirect()->back()->with('error', 'You don\'t have permissions to cancel the order.');
        }

        try {
            DB::transaction(function () use ($order) {
                $cancelledStatusId = OrderStatus::where('name', 'cancelled')->value('id');
                $escrowId = User::where('name', 'escrow_service')->value('id');
                $refundTypeId = TransactionType::where('name', 'refund_escrow')->value('id');

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
                $user_balance = Balance::where('user_id', $order->customer_id)
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
                    'user_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'amount' => $order->budget,
                    'transaction_type_id' => $refundTypeId,
                    'bank_account_id' => null,
                    'related_user_id' => $escrowId,
                    'transfer_uuid' => (string) Str::uuid(),
                    'meta' => [
                        'type' => 'refund_escrow',
                        'recipient' => User::where('id', $order->customer_id)->first()->name,
                    ],
                ]);

                // 7. leaving a comment if the executor cancelled the order
                if (Auth::user()->UserRole->name === 'executor')
                {
                    $order->comments()->create
                    ([
                        'value' => 'Executor cancelled the order.',
                        'user_id' => Auth::id(),
                    ]);
                }

                #Notification
                $receiverId = Auth::user()->UserRole->name === 'customer' ? $order->executor_id : $order->customer_id;
                $receiver = User::find($receiverId);
                Notification::createNotification(
                    $receiver,
                    NotificationType::getByName('order_cancelled'),
                    'Order Cancelled',
                    'The order has been cancelled. ' .
                    '<a href="' . route('order.show-order', $order->id) . '" class="text-decoration-none">"' . e($order->title) . '"</a>.'
                );
            });

            return redirect()->back()->with('success', 'Order cancelled successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    public function extendDeadline(Order $order, Request $request)
    {
        if (Auth::id() !== $order->customer_id) {
            return redirect()->back()->with('error', 'You do not have permission to extend the deadline of this order.');
        }

        if ($order->status()->first()->name !== 'in_progress' && $order->status()->first()->name !== 'expired') {
            return redirect()->back()->with('error', 'Only orders in progress or expired status can have their deadlines extended.');
        }

        try {
            $request->validate(['additional_days' => ['bail', 'required', 'integer', 'min:1', 'max:365'],]);

            $additionalDays = (int)$request->input('additional_days');

            $order->update([
                'deadline_in_days' => $order->deadline_in_days + $additionalDays,
                'deadline_date' => $order->deadline_date
                    ? $order->deadline_date->copy()->addDays($additionalDays)
                    : now()->addDays($additionalDays),
            ]);

            return redirect()->back()->with('success', 'Order deadline extended successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to extend deadline: ' . $e->getMessage());
        }
    }

    public function completeOrder(Order $order)
    {
        if (Auth::id() !== $order->customer_id) 
        {
            return redirect()->back()->with('error', 'You don\'t have permission to complete the order!');
        }

        if ($order->status()->first()->name !== 'in_progress') 
        {
            return redirect()->back()->with('error', 'Only orders in progress status can be completed. You can extend the deadline.');
        }

        try 
        {
            DB::transaction(function () use ($order) 
            {
                $completedStatusId = OrderStatus::where('name', 'completed')->value('id');
                $escrowServiceId = User::where('name', 'escrow_service')->value('id');
                $releaseTypeId = TransactionType::where('name', 'release_escrow')->value('id');
                $refundTypeId = TransactionType::where('name', 'refund_escrow')->value('id');
                $transferTypeId = TransactionType::where('name', 'transfer')->value('id');

                // 1. block the order for update
                $order = Order::lockForUpdate()->find($order->id);

                // check if order is already completed
                if ($order->status_id == $completedStatusId) {
                    throw new \Exception('Order already completed');
                }

                // 2. update order status to completed
                $order->update([
                    'status_id' => $completedStatusId
                ]);

                // 3. lock balances for update
                $customer_balance = Balance::where('user_id', $order->customer_id)
                    ->lockForUpdate()
                    ->first();

                $executor_balance = Balance::where('user_id', $order->executor_id)
                    ->lockForUpdate()
                    ->first(); 

                $escrow_service_balance = Balance::where('user_id', $escrowServiceId)
                    ->lockForUpdate()
                    ->first();

                // 4. check if escrowed amount is sufficient to cover the payment for the executor
                if ($customer_balance->escrowed_amount < $order->budget) 
                {
                    throw new \Exception('Escrow mismatch for the customer balance');
                }

                if ($escrow_service_balance->amount < $order->budget) 
                {
                    throw new \Exception('Balance mismatch for the escrow service balance');
                }

                // 5. change amount for each balance  
                $fee = $this->calculateFee($order->budget);
                $customer_balance->decrement('escrowed_amount', $order->budget);
                $escrow_service_balance->decrement('amount', $order->budget);
                $escrow_service_balance->increment('amount', $fee);
                $executor_balance->increment('amount', $order->budget - $fee);

                // 6. create a transaction record for the payment 
                Transaction::create([
                    'user_id'               =>  $escrowServiceId,
                    'order_id'              =>  $order->id,
                    'amount'                =>  $order->budget,
                    'transaction_type_id'   =>  $releaseTypeId,
                    'bank_account_id'       =>  null,
                    'related_user_id'       =>  $order->customer_id,
                    'transfer_uuid'         =>  (string)Str::uuid(),
                    'meta' => 
                    [
                        'type'      => 'release_escrow',
                        'recipient' => $order->customer,
                    ],
                ]);

                Transaction::create([
                    'user_id'               =>  $order->customer_id,
                    'order_id'              =>  $order->id,
                    'amount'                =>  $order->budget - $fee,
                    'transaction_type_id'   =>  $transferTypeId,
                    'bank_account_id'       =>  null,
                    'related_user_id'       =>  $order->executor_id,
                    'transfer_uuid'         =>  (string)Str::uuid(),
                    'meta' => 
                    [
                        'type'      => 'transfer',
                        'recipient' => $order->executor->name,
                        'comment'   => 'payment for the completed order'
                    ],
                ]);

                Transaction::create([
                    'user_id'               =>  $order->customer_id,
                    'order_id'              =>  $order->id,
                    'amount'                =>  $fee,
                    'transaction_type_id'   =>  $transferTypeId,
                    'bank_account_id'       =>  null,
                    'related_user_id'       =>  $escrowServiceId,
                    'transfer_uuid'         =>  (string)Str::uuid(),
                    'meta' => 
                    [
                        'type'      => 'transfer',
                        'recipient' => 'escrow_service',
                        'comment'   => 'fee for the completed order'
                    ],
                ]);

                Notification::createNotification(
                    User::find($order->executor_id),
                    NotificationType::getByName('order_completed'),
                    'Order completed',
                    'Your order ' .
                        '<a href="' . route('order.show-order', $order->id) . '" class="text-decoration-none">' . e($order->title) . '</a>' .
                        ' has been completed. Open the order page for details. Your payment is  $' . ($order->budget - $fee) . '.'
                );
            });

            return redirect()->back()->with('success', 'Order completed successfully!');
        }
        catch (\Exception $e) 
        {
            return redirect()->back()->with('error', 'Failed to complete order: ' . $e->getMessage());
        }
    }
}