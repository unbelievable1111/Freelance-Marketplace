<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Balance;
use App\Models\OrderStatus;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\OrderApprove;
use Illuminate\Http\Request;
use App\Models\TransactionType;
use App\Models\SubOrderCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderApproveController extends Controller
{
    public function index() {}
    
    // This method validates the incoming request data for approving an order.
    private function validateOrderApproveData(Request $request)
    {
        return $request->validate(
            [
                'proposal_description' =>   ['bail', 'required', 'max:1000'],
                'proposal_budget' =>        ['bail', 'required', 'numeric', 'min:5', 'max:25000'],
                'deadline_in_days' =>       ['bail', 'required', 'integer', 'min:1', 'max:365'],
            ]
        );
    }

    public function showProposals(Request $request)
    {
        // Check if the authenticated user is an executor
        if (Auth::user()->UserRole->name != 'executor') {
            return redirect()->back()->with('error-make-approve', 'Only executors can approve orders.');
        }

        // 1. Base query: Orders approved by current user with status "published"
        $ordersQuery = Order::whereHas('orderApproves', function($q) {
                $q->where('user_id', Auth::user()->id);
            })
            ->whereHas('status', function($q) {
                $q->where('name', 'published');
            })
            ->with(['status', 'orderApproves' => function($q) {
                $q->where('user_id', Auth::user()->id)->orderBy('created_at', 'desc');
            }]);

        // 2. Filter by OrderStatus if provided
        if ($request->query('filterOrderStatus')) {
            $ordersQuery->where('status_id', $request->query('filterOrderStatus'));
            $currentOrderStatusFilter = OrderStatus::find($request->query('filterOrderStatus'));
        } else {
            $currentOrderStatusFilter = (object) ['id' => 0, 'name' => 'All Statuses'];
        }

        // 3. Filter by SubOrderCategory if provided
        if ($request->query('filterSubcategory')) {
            $ordersQuery->where('sub_category_id', $request->query('filterSubcategory'));
            $currentSubcategoryFilter = SubOrderCategory::find($request->query('filterSubcategory'));
        } else {
            $currentSubcategoryFilter = (object) ['id' => 0, 'name' => 'All Subcategories'];
        }

        // 4. Apply sorting based on user selection
        switch ($request->query('sortType')) {
            case 'byTimeAsc':
                // Sort by the earliest approve date of the current user
                $ordersQuery->orderBy(
                    OrderApprove::select('created_at')
                        ->whereColumn('order_id', 'orders.id')
                        ->where('user_id', Auth::user()->id)
                        ->limit(1), 'asc'
                );
                break;

            case 'byTimeDesc':
                $ordersQuery->orderByDesc(
                    OrderApprove::select('created_at')
                        ->whereColumn('order_id', 'orders.id')
                        ->where('user_id', Auth::user()->id)
                        ->limit(1)
                );
                break;

            case 'byBudgetAsc':
                $ordersQuery->orderBy('budget', 'asc');
                break;

            case 'byBudgetDesc':
                $ordersQuery->orderBy('budget', 'desc');
                break;

            case 'byStatus':
                $ordersQuery->orderBy('status_id', 'asc');
                break;

            default:
                // Default sort: newest approve date first
                $ordersQuery->orderByDesc(
                    OrderApprove::select('created_at')
                        ->whereColumn('order_id', 'orders.id')
                        ->where('user_id', Auth::user()->id)
                        ->limit(1)
                );
                break;
        }

        // 5. Paginate the results
        $orders = $ordersQuery->paginate(10, ['*'], 'p')->withQueryString();

        // 6. Check deadlines
        foreach ($orders as $order) { 
            $order->checkDeadline();
        }

        // 7. Get unique subcategories for filters
        $allOrdersForUser = Order::whereHas('orderApproves', function($q) {
            $q->where('user_id', Auth::user()->id);
        })->get();
        $uniqueSubcategories = SubOrderCategory::getUniqueSubcategoriesFromOrders($allOrdersForUser);

        // 8. Get all order statuses
        $uniqueOrderStatuses = OrderStatus::all();

        // 9. Total count of orders for executor
        $orders_total_count = Auth::user()->ordersAsExecutor()->count();

        // Render the view
        return view('components.pages.orders.proposals', compact(
            'orders',
            'orders_total_count',
            'uniqueSubcategories',
            'uniqueOrderStatuses',
            'currentSubcategoryFilter',
            'currentOrderStatusFilter'
        ));
    }

    public function submit(Order $order, OrderApprove $orderApproval)
    {
        # 1. Check if the user is an customer
        if (Auth::user()->UserRole->name !== 'customer') {
            return redirect()->back()->with('error-order-approve', 'Only customers can submit proposals.');
        }

        # 2. Check if the user is the owner of the order
        if ($order->customer_id !== Auth::id()) {
            return  redirect()->back()->with('error-order-approve', 'You can only submit your own proposals.');
        }

        # 3. Check if the order is in a state that can be approved
        if ($order->status->name !== 'published') {
            return  redirect()->back()->with('error-order-approve', 'Only orders with published status can be approved.');
        }

        # 4. Check if the approval belongs to the order
        if ($orderApproval->order_id !== $order->id) {
            return  redirect()->back()->with('error-order-approve', "This proposal does not belong to the specified order. $orderApproval->order_id !== $order->id");
        }

        # 5. If the proposed budget is different from the current order budget, we need to handle the financial transactions accordingly. 
        try {
            DB::transaction(function () use ($order, $orderApproval) {
                if ($order->budget != $orderApproval->proposed_budget) 
                {
                    $budgetDifference = $orderApproval->proposed_budget - $order->budget;
                    $escrowId = User::where('name', 'escrow_service')->value('id');

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
                    } elseif ($budgetDifference < 0) {
                        $user_balance->increment('amount', abs($budgetDifference));
                        $user_balance->decrement('escrowed_amount', abs($budgetDifference));
                        $escrow_service_balance->decrement('amount', abs($budgetDifference));

                        Transaction::create(
                        [
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
                }

                $order->status_id = OrderStatus::where('name', 'in_progress')->value('id');
                $order->executor_id = $orderApproval->user_id;
                $order->budget = $orderApproval->proposed_budget;
                $order->deadline_in_days = $orderApproval->proposed_deadline_in_days;
                $order->deadline_date = now()->addDays($orderApproval->proposed_deadline_in_days);
                $order->update();
            });

            return redirect()->back()->with('success', 'Proposal submitted successfully.');
        }
        catch (\Exception $e) 
        {
            return redirect()->back()->with('error-order-approve', 'Failed to submit proposal: ' . $e->getMessage());
        }

        # 6. Here you would typically handle the submission logic, such as marking the proposal as submitted, notifying the client, etc.
        // For demonstration purposes, we'll just return a success message.

        return redirect()->back()->with('success', 'Proposal submitted successfully.');
    }

    public function update(Request $request, Order $order)
    {
        # 1. Check if the user is an executor
        if (Auth::user()->UserRole->name !== 'executor') {
            return redirect()->route('home.index')->with('error', 'Only executors can edit their proposals.');
        }

        # 2. Validate the incoming request data
        $validatedData = $this->validateOrderApproveData($request);

        # 3. Check if the order is in a state that can be approved
        if ($order->status->name !== 'published') {
            return redirect()->back()->with('error', 'Only orders with published status can be approved.');
        }

        # 4. Check if the user has already approved this order
        $orderApprove = $order->orderApproves()->where('user_id', Auth::id())->first();
        if (!$orderApprove) {
            return redirect()->back()->with('error', 'You have not approved this order yet.');
        }

        # 5. Update the existing order approval record
        $orderApprove->update(
            [
                'comment'                           => $validatedData['proposal_description'],
                'proposed_budget'                   => $validatedData['proposal_budget'],
                'proposed_deadline_in_days'         => $validatedData['deadline_in_days'],
            ]
        );

        return redirect()->back()->with('success', 'Proposal updated successfully.');
    }

    public function cancel(Order $order)
    {
        # 1. Check if the user is an executor
        if (Auth::user()->UserRole->name !== 'executor') {
            return redirect()->route('home.index')->with('error', 'Only executors can cancel their proposals.');
        }

        # 2. Check if the order is in a state that can be approved
        if ($order->status->name !== 'published') {
            return redirect()->back()->with('error', 'Only orders with published status can be approved.');
        }

        # 3. Check if the user has already approved this order
        $orderApprove = $order->orderApproves()->where('user_id', Auth::id())->first();
        if (!$orderApprove) {
            return redirect()->back()->with('error', 'You have not approved this order yet.');
        }

        # 4. Delete the existing order approval record
        $orderApprove->delete();

        return redirect()->back()->with('success', 'Proposal cancelled successfully.');
    }

    public function makeApprove(Request $request, Order $order)
    {
        # 1. Check if the user is an executor
        if (Auth::user()->UserRole->name !== 'executor') 
        {
            return  redirect()->back()->with('error-make-approve', 'Only executors can approve orders.');
        }

        # 2. Validate the incoming request data
        $validatedData = $this->validateOrderApproveData($request);

        # 3. Check if the order is in a state that can be approved
        if ($order->status->name !== 'published') 
        {
            return redirect()->back()->with('error-make-approve', 'Only orders with published status can be approved.');
        }

        # 4. Check if the user has already approved this order
        if ($order->orderApproves()->where('user_id', Auth::id())->exists()) {
            return redirect()->back()->with('error-make-approve', 'You have already approved this order.');
        }

        # 5. Create a new order approval record
        $order->orderApproves()->create(
            [
                'user_id'                           => Auth::id(),
                'order_id'                          => $order->id,
                'comment'                           => $validatedData['proposal_description'],
                'proposed_budget'                   => $validatedData['proposal_budget'],
                'proposed_deadline_in_days'         => $validatedData['deadline_in_days'],
            ]
        );

        return redirect()->back()->with('success', 'Order approve were created successfully.');
    }
}