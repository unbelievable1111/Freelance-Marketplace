<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::where('target_id', auth()->id())
            ->latest()
            ->paginate(5); 

        return view('components.pages.profile.personal-reviews', compact('reviews'));
    }

    public function leaveReview(Request $request, Order $order)
    {
        $request->validate(
        [
            'feedback' => 'required|string|max:1500',
            'score' => 'required|integer|min:1|max:5',
        ]);

        if ($order->status->name !== 'completed')
        {
            return redirect()->back()->with('leave-review-error', 'You can only leave a review for completed orders.');
        }

        if ($order->customer_id !== auth()->id() && $order->executor_id !== auth()->id())
        {
            return redirect()->back()->with('leave-review-error', 'You can only leave a review for orders you have completed or executed.');
        }

        if (auth()->user()->UserRole->name === 'customer' && $order->hasReviewForExecutor())
        {
            return redirect()->back()->with('leave-review-error', 'You have already left a review for this order. Only one review per order is allowed.');
        }

        if (auth()->user()->UserRole->name === 'executor' && $order->hasReviewForCustomer())
        {
            return redirect()->back()->with('leave-review-error', 'You have already left a review for this order. Only one review per order is allowed.');
        }

        if ($order->customer_id === auth()->id())
        {
            $targetUserId = $order->executor_id;
        }
        else
        {
            $targetUserId = $order->customer_id;
        }

        $review = new Review();
        $review->order_id = $order->id;
        $review->author_id = auth()->id();
        $review->target_id = $targetUserId;
        $review->feedback = $request->input('feedback');
        $review->score = $request->input('score');
        $review->save();

        return redirect()->route('order.show-order', ['order' => $order->id])->with('leave-review-success', 'Your review has been submitted successfully.');
    }

    public function delete(Review $review)
    {
        if ($review->author_id !== auth()->id())
        {
            return redirect()->back()->with('delete-review-error', 'You can only delete your own reviews.');
        }

        $review->delete();

        return redirect()->back()->with('delete-review-success', 'Your review has been deleted successfully.');
    }
}