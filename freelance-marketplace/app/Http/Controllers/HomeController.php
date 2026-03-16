<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\SubOrderCategory;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $ordersQuery = Order::with(['status', 'subCategory'])
            ->whereHas('status', function ($q) {
                $q->where('name', 'published');
            });

        $currentSubcategoryFilter = null;

        if ($request->query('filterSubcategory')) {

            $currentSubcategoryFilter = SubOrderCategory::find($request->query('filterSubcategory'));

            if ($currentSubcategoryFilter) {
                $ordersQuery->where('sub_category_id', $currentSubcategoryFilter->id);
            }
        } else {

            $currentSubcategoryFilter = (object)[
                'id' => 0,
                'name' => 'All Subcategories'
            ];
        }

        switch ($request->query('sortType')) {

            case 'byTimeAsc':
                $ordersQuery->orderBy('created_at', 'asc');
                break;

            case 'byTimeDesc':
                $ordersQuery->orderBy('created_at', 'desc');
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
                $ordersQuery->orderBy('created_at', 'desc');
                break;
        }

        $orders_total_count = (clone $ordersQuery)->count();

        $orders = $ordersQuery
            ->paginate(10)
            ->withQueryString();

        $uniqueSubcategories = SubOrderCategory::whereIn(
            'id',
            Order::whereHas('status', function ($q) {
                $q->where('name', 'published');
            })
            ->distinct()
            ->pluck('sub_category_id')
        )->get();

        $uniqueOrderStatuses = OrderStatus::all();

        return view(
            'components.pages.home.index',
            compact(
                'orders',
                'uniqueSubcategories',
                'uniqueOrderStatuses',
                'currentSubcategoryFilter',
                'orders_total_count'
            )
        );
    }
}