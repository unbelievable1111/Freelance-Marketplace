<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('components.pages.profile.bank-accounts');
    }

    public function deleteCard(BankAccount $bankAccount)
    {
        if (Auth::id() !== $bankAccount->user_id) {
            return back()->with('failure', 'Such card doesn\'t belong to such user!');
        }

        $bankAccount->delete();

        return back()->with('success', 'The card deleted!');
    }

    public function createCard(Request $request)
    {
        $cards_count = BankAccount::where('user_id', '=', Auth::id())->count();

        if ($cards_count >= 5) {
            return back()->with('creation-card-failure', 'You can\'t add more than 5 cards!');
        }
        
        $validated = $request->validate([
            'name' => [ 'required', 'string', 'max:64' ],
            'card_number' => [
                'required',
                'digits:16',
                'unique:bank_accounts,card_number'
            ],
        ]);

        BankAccount::create([
            'name' => $validated['name'],
            'card_number' => $validated['card_number'],
            'user_id' => Auth::id(),
        ]);

        return back()->with('creation-card-success', 'Card added successfully!');
    }
}