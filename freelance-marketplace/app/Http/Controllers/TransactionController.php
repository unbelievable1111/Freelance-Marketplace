<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    const TRANSACTIONS_PER_PAGE = 15;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('components.pages.profile.finance-operations');
    }

    public function history(Request $request)
    {
        $userId = Auth::id();

        $order = $request->query('order') === 'desc' ? 'desc' : 'asc';
        $perPage = self::TRANSACTIONS_PER_PAGE;
        $page = $request->query('page', 1);
        $totalPages = ceil(Transaction::where('user_id', $userId)
            ->orWhere('related_user_id', $userId)
            ->count() / $perPage);

        $transactions = Transaction::where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('related_user_id', $userId);
            })
            ->orderBy('created_at', $order)
            ->paginate($perPage, ['*'], 'page', $request->query('page'))
            ->withQueryString();    

        return view('components.pages.profile.transaction-history', compact(['transactions', 'order', 'page', 'totalPages']));
    }

    public function deposit(Request $request)
    {
        $validatedData = $request->validate([
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:10000'],
        ]);

        $bankAccount = BankAccount::findOrFail($validatedData['bank_account_id']);

        if ($bankAccount->user_id !== Auth::id()) {
            return back()->with(
                'deposit_failure',
                'You can\'t deposit from a card that doesn\'t belong to you!'
            );
        }

        try 
        {
            DB::transaction(function () use ($validatedData, $bankAccount) {
                $balance = Balance::where('user_id', Auth::id())->lockForUpdate()->first();

                if (!$balance) {
                    throw new \Exception('Balance not found for this user!');
                }

                $balance->increment('amount', $validatedData['amount']);

                $transactionTypeId = TransactionType::where('name', 'deposit')->value('id');

                Transaction::create([
                    'user_id' => Auth::id(),
                    'amount' => $validatedData['amount'],
                    'transaction_type_id' => $transactionTypeId,
                    'bank_account_id' => $bankAccount->id,
                    'related_user_id' => null,
                    'transfer_uuid' => (string) Str::uuid(),
                    'meta' => [
                        'type'        => 'deposit',
                        'card_number' => $bankAccount->card_number,
                        'recipient'   => Auth::user()->name,
                    ],
                ]);
            });

            return back()->with('deposit_success', 'Deposit completed successfully!');
        } catch (\Exception $e) {
            return back()->with('deposit_failure', $e->getMessage());
        }
    }

    public function withdraw(Request $request)
    {
        $validatedData = $request->validate([
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $bankAccount = BankAccount::findOrFail($validatedData['bank_account_id']);

        if ($bankAccount->user_id !== Auth::id()) {
            return back()->with(
                'withdraw_failure',
                'You can\'t withdraw to a card that doesn\'t belong to you!'
            );
        }

        try {
            DB::transaction(function () use ($validatedData, $bankAccount) {
                $balance = Balance::where('user_id', Auth::id())->lockForUpdate()->first();

                if (!$balance || $balance->amount < $validatedData['amount']) {
                    throw new \Exception('You cannot withdraw an amount exceeding the amount in your balance!');
                }

                $balance->decrement('amount', $validatedData['amount']);

                $transactionTypeId = TransactionType::where('name', 'withdraw')->value('id');

                Transaction::create([
                    'user_id' => Auth::id(),
                    'amount' => $validatedData['amount'],
                    'transaction_type_id' => $transactionTypeId,
                    'bank_account_id' => $bankAccount->id,
                    'related_user_id' => null,
                    'transfer_uuid' => (string) Str::uuid(),
                    'meta' => [
                        'type'        => 'withdraw',
                        'card_number' => $bankAccount->card_number,
                        'recipient'   => Auth::user()->name,
                    ],
                ]);
            });

            return back()->with('withdraw_success', 'Withdraw completed successfully!');
        } catch (\Exception $e) {
            return back()->with('withdraw_failure', $e->getMessage());
        }
    }
}