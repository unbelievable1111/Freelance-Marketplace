<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\Order;
use App\Models\Report;
use App\Models\ReportStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     **/
    public function index()
    {
        $reports = Report::where('reporter_id', auth()->id())
            ->latest()
            ->paginate(10);

        // Admins can see all reports
        if (auth()->user()->isAdmin()) {
            $reports = Report::latest()->paginate(10);

            return view('components.pages.reports.index', compact('reports'));
        }

        // Customers and executors can see only their own reports
        if (auth()->user()->isCustomer() || auth()->user()->isExecutor()) {
            $reports = Report::where('reporter_id', auth()->id())->latest()->paginate(10);
            return view('components.pages.profile.reports', compact('reports'));
        }
    }

    public function show(Report $report)
    {
        // Check if the user is authorized to view the report
        if (auth()->id() !== $report->reporter_id && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $comments = $report->comments()->latest()->paginate(10);

        return view('components.pages.reports.show', compact('report', 'comments'));
    }

    public function storeComment(Request $request, Report $report)
    {
        // Check if the user is authorized to comment on the report
        if (auth()->id() !== $report->reporter_id && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        if ($report->status->name === 'completed') {
            return redirect()
                ->route('report.show', $report)
                ->with('error', 'Cannot add comments to a completed report.');
        }

        $validated = $request->validate(
        [
            'content' => ['required', 'string', 'max:1000'],
        ], 
        [
            'content.required' => 'Comment content is required',
            'content.max' => 'Comment content is too long (max 1000 chars)',
        ]);

        $report->comments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        # Notify the other party (reporter or admins)
        $recipients[] = null;

        if (auth()->user()->isAdmin()) 
        {
            $recipients[] = $report->reporter;
        }
        else 
        {
            $admins = User::whereHas('userRole', function ($query) 
            {
                $query->where('name', 'admin');
            })->get();

            foreach ($admins as $admin) 
            {
                $recipients[] = $admin;
            }
        }

        foreach ($recipients as $recipient) 
        {
            if (!$recipient) {
                continue;
            }

            $reportLink  = route('report.show', $report);
            $orderLink   = route('order.show-order', $report->order);
            $profileLink = route('public-profile.overview', auth()->id());
            
            Notification::createNotification(
                $recipient,
                NotificationType::getByName('report_answer_received'),
                'Report answer received',
                sprintf(
                    'A new comment has been added to the report for order <a href="%s" class="text-decoration-none">"%s"</a> by user <a href="%s" class="text-decoration-none">%s</a>. Open the <a href="%s" class="text-decoration-none">report</a> to view it.',
                    $orderLink,
                    e($report->order->title),
                    $profileLink,
                    e(auth()->user()->name),
                    $reportLink
                )
            );
        }

        return redirect()
            ->route('report.show', $report)
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Order $order)
    {
        return view('components.pages.reports.create', compact('order'));
    }

    /**
     ** Store a newly created resource in storage.
     **/
    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'reason'      => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Please select a reason',
            'reason.max' => 'Reason is too long',
            'description.max' => 'Description is too long (max 1000 chars)',
        ]);

        $userId = auth()->id();

        
        $alreadyReported = Report::where('reporter_id', $userId)
            ->where('order_id', $order->id)
            ->exists();

        if ($alreadyReported) {
            return redirect()
                ->route('order.show-order', $order)
                ->with('error', 'You have already reported this order.');
        }

        DB::transaction(function () use ($validated, $order, $userId) {
            $report = Report::create([
                'order_id'    => $order->id,
                'reporter_id' => $userId,
                'title'       => $validated['reason'],
                'description' => $validated['description'] ?? null,
                'status_id'   => ReportStatus::getStatusByName('in_progress')->id,
            ]);

            $admins = User::whereHas('userRole', function ($query) {
                $query->where('name', 'admin');
            })->get();

            $reportLink  = route('report.show', $report);
            $orderLink   = route('order.show-order', $order);
            $profileLink = route('public-profile.overview', $userId);

            foreach ($admins as $admin) {
                Notification::createNotification(
                    $admin,
                    NotificationType::getByName('report_started'),
                    'You have received a report for order',
                    sprintf(
                        'You have received a new report on order <a href="%s" class="text-decoration-none">"%s"</a> from user <a href="%s" class="text-decoration-none">%s</a>. Open the <a href="%s" class="text-decoration-none">report</a> to view it.',
                        $orderLink,
                        e($order->title),
                        $profileLink,
                        e(auth()->user()->name),
                        $reportLink 
                    )
                );
            }
        });

        return redirect()
            ->route('order.show-order', $order)
            ->with('success', 'Report submitted successfully.');
    }

    function complete(Report $report)
    {
        // Only admins can complete reports
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $report->status_id = ReportStatus::getStatusByName('completed')->id;
        $report->save();

        Notification::createNotification(
            $report->reporter,
            NotificationType::getByName('report_completed'),
            'Report completed',
            sprintf(
                'The report for order <a href="%s" class="text-decoration-none">"%s"</a> has been completed. The <a href="%s" class="text-decoration-none">report</a> is now closed and no more comments can be added.',
                route('order.show-order', $report->order),
                $report->order->title,
                route('report.show', $report)
            )
        );

        return redirect()
            ->route('report.show', $report)
            ->with('success', 'Report marked as completed.');
    }
}