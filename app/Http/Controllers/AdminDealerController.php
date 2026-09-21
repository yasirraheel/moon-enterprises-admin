<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Orders;
use App\Models\Deposits;
use App\Models\Withdrawals;
use App\Models\Categories;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AdminDealerController extends Controller
{
    /**
     * Display dealership requests
     */
    public function dealershipRequests()
    {
        if (!auth()->user()->hasPermission('dealership_requests')) {
            return view('admin.unauthorized');
        }

        $allRequests = User::dealershipRequests()->latest('date')->paginate(20);
        $pendingRequests = User::where('dealer_status', 'pending')->latest('date')->paginate(20);
        $approvedRequests = User::where('dealer_status', 'approved')->latest('date')->paginate(20);
        $rejectedRequests = User::where('dealer_status', 'rejected')->latest('date')->paginate(20);

        return view('admin.dealership-requests', compact(
            'allRequests',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests'
        ));
    }

    /**
     * Approve dealership request
     */
    public function approveDealershipRequest(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:users,id',
            'commission' => 'required|numeric|min:0|max:100',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        $user = User::findOrFail($request->request_id);

        if ($user->dealer_status !== 'pending') {
            return back()->with('error_message', 'This request has already been processed.');
        }

        // Update user to dealer
        $user->dealer_status = 'approved';
        $user->dealer_commission = $request->commission;
        $user->save();

        // Send notification to user
        NotificationService::notifyDealershipApproved($user->id, $request->commission);

        return back()->with('success_message', 'Dealership request approved. User ' . $user->username . ' is now a dealer.');
    }

    /**
     * Reject dealership request
     */
    public function rejectDealershipRequest(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:users,id',
            'admin_notes' => 'required|string|max:1000'
        ]);

        $user = User::findOrFail($request->request_id);

        if ($user->dealer_status !== 'pending') {
            return back()->with('error_message', 'This request has already been processed.');
        }

        $user->dealer_status = 'rejected';
        $user->dealer_commission = 0;
        $user->save();

        // Send notification to user
        NotificationService::notifyDealershipRejected($user->id, $request->admin_notes);

        return back()->with('success_message', 'Dealership request rejected.');
    }

    /**
     * Display dealers list
     */
    public function dealers(Request $request)
    {
        if (!auth()->user()->hasPermission('dealers')) {
            return view('admin.unauthorized');
        }

        $query = request()->get('q');

        if ($query != '' && strlen($query) > 2) {
            $data = User::dealers()
                ->where(function($q) use ($query) {
                    $q->where('full_name', 'LIKE', '%'.$query.'%')
                      ->orWhere('username', 'LIKE', '%'.$query.'%')
                      ->orWhere('phone', 'LIKE', '%'.$query.'%');
                })
                ->orderBy('id','desc')
                ->paginate(20);
        } else {
            $data = User::dealers()
                ->orderBy('id','desc')
                ->paginate(20);
        }

        return view('admin.members', compact('data', 'query'))->with('filterDealers', true);
    }

    /**
     * Display dealer orders (orders from dealers only)
     */
    public function dealerOrders(Request $request)
    {
        if (!auth()->user()->hasPermission('dealer_orders')) {
            return view('admin.unauthorized');
        }

        $query = Orders::whereHas('user', function($q) {
            $q->dealers();
        });

        // Filter by category if provided
        if ($request->has('category') && !empty($request->category)) {
            $categoryName = $request->category;
            $query->where('game_name', 'like', '%' . $categoryName . '%');
        }

        // Filter by dealer if provided
        if ($request->has('dealer_id') && !empty($request->dealer_id)) {
            $query->where('user_id', $request->dealer_id);
        }

        // RTTP filter
        if ($request->has('rttp_filter') && !empty($request->rttp_filter)) {
            $rttpTerm = $request->rttp_filter;
            $query->where('rttp', $rttpTerm);
            $data = $query->orderBy('id', 'DESC')->get();
        } else {
            // Regular search
            if ($request->has('q') && !empty($request->q)) {
                $searchTerm = $request->q;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('username', 'like', '%' . $searchTerm . '%')
                      ->orWhere('user_phone', 'like', '%' . $searchTerm . '%')
                      ->orWhere('game_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('bond_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('rttp', 'like', '%' . $searchTerm . '%')
                      ->orWhere('first', 'like', '%' . $searchTerm . '%')
                      ->orWhere('second', 'like', '%' . $searchTerm . '%');
                });
            }

            $data = $query->orderBy('id', 'DESC')->paginate(20);
        }

        $categories = Categories::orderBy('name')->get();
        $dealers = User::dealers()->orderBy('username')->get();

        return view('admin.orders', compact('data', 'categories', 'dealers'))->with('filterDealers', true);
    }

    /**
     * Display dealer deposits
     */
    public function dealerDeposits(Request $request)
    {
        if (!auth()->user()->hasPermission('dealer_deposits')) {
            return view('admin.unauthorized');
        }

        $query = Deposits::whereHas('user', function($q) {
            $q->dealers();
        });

        // Filter by dealer if provided
        if ($request->has('dealer_id') && !empty($request->dealer_id)) {
            $query->where('user_id', $request->dealer_id);
        }

        $allDeposits = (clone $query)->with(['user', 'paymentMethod'])->latest()->paginate(20);

        $pendingDeposits = (clone $query)->where('status', 'pending')->with(['user', 'paymentMethod'])->latest()->paginate(20);

        $approvedDeposits = (clone $query)->where('status', 'approved')->with(['user', 'paymentMethod'])->latest()->paginate(20);

        $rejectedDeposits = (clone $query)->where('status', 'rejected')->with(['user', 'paymentMethod'])->latest()->paginate(20);

        $dealers = User::dealers()->orderBy('username')->get();

        return view('admin.deposits', compact(
            'allDeposits',
            'pendingDeposits',
            'approvedDeposits',
            'rejectedDeposits',
            'dealers'
        ))->with('filterDealers', true);
    }

    /**
     * Display dealer withdrawals
     */
    public function dealerWithdrawals(Request $request)
    {
        if (!auth()->user()->hasPermission('dealer_withdrawals')) {
            return view('admin.unauthorized');
        }

        $query = Withdrawals::whereHas('user', function($q) {
            $q->dealers();
        });

        // Filter by dealer if provided
        if ($request->has('dealer_id') && !empty($request->dealer_id)) {
            $query->where('user_id', $request->dealer_id);
        }

        $allWithdrawals = (clone $query)->with(['user'])->latest()->paginate(20);

        $pendingWithdrawals = (clone $query)->where('status', 'pending')->with(['user'])->latest()->paginate(20);

        $approvedWithdrawals = (clone $query)->where('status', 'approved')->with(['user'])->latest()->paginate(20);

        $rejectedWithdrawals = (clone $query)->where('status', 'rejected')->with(['user'])->latest()->paginate(20);

        $dealers = User::dealers()->orderBy('username')->get();

        return view('admin.withdrawals', compact(
            'allWithdrawals',
            'pendingWithdrawals',
            'approvedWithdrawals',
            'rejectedWithdrawals',
            'dealers'
        ))->with('filterDealers', true);
    }

    /**
     * Display dealer transactions
     */
    public function dealerTransactions(Request $request)
    {
        if (!auth()->user()->hasPermission('transactions')) {
            return view('admin.unauthorized');
        }

        $query = Transaction::whereHas('user', function($q) {
            $q->dealers();
        })->with(['user']);

        // Filter by user if provided
        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by transaction type if provided
        if ($request->has('transaction_type') && !empty($request->transaction_type)) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by type (credit/debit) if provided
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('reference_id', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                      $userQuery->where('username', 'like', '%' . $searchTerm . '%')
                                ->orWhere('phone', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        $data = $query->orderBy('created_at', 'DESC')->paginate(20);

        // Get dealer users for filter dropdown
        $users = User::dealers()->orderBy('username')->get();

        return view('admin.transactions', compact('data', 'users'))->with('filterDealers', true);
    }

    /**
     * Remove dealer status from user
     */
    public function removeDealerStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->dealer_status = 'na';
        $user->dealer_commission = 0;
        $user->save();

        return back()->with('success_message', 'Dealer status removed from ' . $user->username);
    }
}
