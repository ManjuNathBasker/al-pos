<?php

namespace App\Http\Controllers;

use App\Models\RegisterSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterSessionController extends Controller
{
    /**
     * Display a listing of past register sessions (for admin reporting).
     */
    public function index()
    {
        $this->authorize('view accounts'); // Or a specific shift reporting permission
        
        $sessions = RegisterSession::with('user')
            ->orderBy('opened_at', 'desc')
            ->paginate(15);
            
        return view('register_sessions.index', compact('sessions'));
    }

    /**
     * Store a newly created register session.
     */
    public function store(Request $request)
    {
        $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        // Check if user already has an open session
        $openSession = RegisterSession::openForUser(Auth::id())->first();
        if ($openSession) {
            return redirect()->back()->with('error', 'You already have an open register session.');
        }

        $session = RegisterSession::create([
            'company_id' => session('company_id'),
            'user_id' => Auth::id(),
            'opened_at' => now(),
            'opening_amount' => $request->opening_amount,
            'status' => 'open',
        ]);

        \App\Models\CashTransaction::create([
            'register_session_id' => $session->id,
            'type' => 'OPENING_BALANCE',
            'amount' => $request->opening_amount,
            'payment_method' => 'Cash',
            'description' => 'Opening Balance',
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Register opened successfully.');
    }

    /**
     * Close the specified register session.
     */
    public function close(Request $request, RegisterSession $registerSession)
    {
        // Ensure user is closing their own register, or is an admin.
        if ($registerSession->user_id !== Auth::id() && !Auth::user()->can('manage accounts')) {
            abort(403);
        }

        if ($registerSession->status === 'closed') {
            return redirect()->back()->with('error', 'This register session is already closed.');
        }

        $request->validate([
            'closing_amount_actual' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $expectedAmount = $registerSession->calculateExpectedAmount();
        $actualAmount = $request->closing_amount_actual;
        $difference = $actualAmount - $expectedAmount;

        $registerSession->update([
            'closed_at' => now(),
            'closing_amount_expected' => $expectedAmount,
            'closing_amount_actual' => $actualAmount,
            'difference' => $difference,
            'status' => 'closed',
            'notes' => $request->notes,
        ]);

        \App\Models\CashTransaction::create([
            'register_session_id' => $registerSession->id,
            'type' => 'CLOSING_BALANCE',
            'amount' => $actualAmount,
            'payment_method' => 'Cash',
            'description' => 'Closing Balance recorded',
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Register closed successfully. Difference: $' . number_format($difference, 2));
    }
}
