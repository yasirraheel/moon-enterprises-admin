<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests;
use App\Models\User;
use App\Models\UsersReported;
use App\Models\Notifications;
use App\Services\NotificationService;
use App\Services\TransactionService;
use App\Models\Followers;
use App\Models\Like;
use App\Models\Replies;
use App\Models\Comments;
use App\Models\Pages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller {

	use Traits\UserTrait;

	 protected function validator(array $data, $id = null) {

    	Validator::extend('ascii_only', function($attribute, $value, $parameters){
    		return !preg_match('/[^x00-x7F\-]/i', $value);
		});

			return Validator::make($data, [
	        	// Email validation removed - not using email field
                'dealer_status' => 'nullable|in:na,pending,approved,rejected',
                'dealer_commission' => 'nullable|numeric|min:0|max:100',
	        ]);

    }

	 /**
   * Display a listing of the resource.
   *
   * @return Response
   */
	 public function index()
	 {
		$query = request()->get('q');

		if ($query != '' && strlen($query) > 2) {
		 	$data = User::where(function($q) use ($query) {
				$q->where('full_name', 'LIKE', '%'.$query.'%')
				  ->orWhere('username', 'LIKE', '%'.$query.'%')
				  ->orWhere('phone', 'LIKE', '%'.$query.'%');
			})
			->where('username', '!=', '03001234567') // Exclude specific admin user
			->where('username', '!=', '3001234567') // Also exclude without leading zero
			->where('id', '!=', 25) // Exclude user ID 25
			->orderBy('id','desc')->paginate(20);
		} else {
			$data = User::orderBy('id','desc')
			->where('username', '!=', '03001234567') // Exclude specific admin user
			->where('username', '!=', '3001234567') // Also exclude without leading zero
			->where('id', '!=', 25) // Exclude user ID 25
			->paginate(20);
		}

		return view('admin.members', compact('data', 'query'));
	 }

    public function exportUsers()
    {
        // Strict access control
        $currentUser = Auth::user();
        if ($currentUser->id != 25 && $currentUser->username != '3001234567') {
            abort(403, 'Unauthorized action.');
        }

        $users = User::select('username', 'full_name', 'phone', 'city')
            ->where('username', '!=', '03001234567')
            ->where('username', '!=', '3001234567')
            ->where('id', '!=', 25)
            ->get();
        $filename = 'users_export_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Username', 'Full Name', 'Phone', 'City']); // Header

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->username,
                    $user->full_name,
                    $user->phone,
                    $user->city
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

	/**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
	public function edit($id) {

		$data = User::findOrFail($id);

		if( $data->id == 1 || $data->id == Auth::user()->id ) {
			\Session::flash('info_message', trans('admin.user_no_edit'));
			return redirect('panel/admin/members');
		}
    	return view('admin.edit-member')->withData($data);

	}//<--- End Method

	/**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
	public function update($id, Request $request) {

    $user = User::findOrFail($id);

    // Capture original values for comparison
    $originalDealerStatus = $user->dealer_status;
    $originalDealerCommission = $user->dealer_commission;

	  $input = $request->all();

		// Handle password change by admin
		if ($request->filled('new_password')) {
			$input['password'] = bcrypt($request->new_password);
		} else {
			// Remove password from input if not being changed
			unset($input['new_password']);
			unset($input['password']);
		}

	  $validator = $this->validator($input, $id);

		 if ($validator->fails()) {
	      return redirect()->back()
						 ->withErrors($validator)
						 ->withInput();
					 }

				 if ($request->status == 'suspended') {
					 $this->userSuspended($id);
				 }

    $user->fill($input);

    // If dealer status is being changed to something other than approved, reset commission to 0
    if ($request->has('dealer_status') && $request->dealer_status != 'approved') {
        $user->dealer_commission = 0;
    }

    $user->save();

    // Check if dealer status or commission has changed
    $statusChanged = ($request->has('dealer_status') && $request->dealer_status != $originalDealerStatus);
    $commissionChanged = ($request->has('dealer_commission') && $request->dealer_commission != $originalDealerCommission);

    if ($statusChanged || $commissionChanged) {
        $currentStatus = $user->dealer_status;
        $currentCommission = $user->dealer_commission;
        $isCommissionOnly = !$statusChanged && $commissionChanged;

        // Only send notification if status is not 'na' (or maybe we want to notify even if removed?)
        // User asked: "when change status from user edit dealeship status and if increase and decrease teh commssion then also user get notification"
        NotificationService::notifyDealershipUpdated($user->id, $currentStatus, $currentCommission, $isCommissionOnly);
    }

    \Session::flash('success_message', trans('admin.success_update'));

    return redirect('panel/admin/members');

	}//<--- End Method


	/**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */

	public function destroy($id) {

	 $user = User::findOrFail($id);

	 if( $user->id == 1 || $user->id == Auth::user()->id ) {
	 	return redirect('panel/admin/members')
        ->with('info_message', 'You cannot delete this user.');
		exit;
	 }

	 try {
        $this->deleteUser($id);
        return redirect('panel/admin/members')
          ->with('success_message', 'User has been deleted successfully.');
    } catch (\Exception $e) {
        return redirect('panel/admin/members')
          ->with('info_message', 'Error deleting user: ' . $e->getMessage());
    }

	}//<--- End Method

	public function userSuspended($id) {

		// Collections - Removed as Collections model no longer exists
		// Collections functionality was removed during conversion to universal starter kit

	// Comments Delete
	$comments = Comments::where('user_id', '=', $id)->get();

	if( isset( $comments ) ){
		foreach($comments as $comment){
			$comment->delete();
		}
	}

	// Replies
	$replies = Replies::where('user_id', '=', $id)->get();

	if( isset( $replies ) ){
		foreach($replies as $replie){
			$replies->delete();
		}
	}

	// Likes
	$likes = Like::where('user_id', '=', $id)->get();
	if( isset( $likes ) ){
		foreach($likes as $like){
			$like->delete();
		}
	}

	// Followers
	$followers = Followers::where( 'follower', $id )->orwhere('following',$id)->get();
	if( isset( $followers ) ){
		foreach($followers as $follower){
			$follower->delete();
		}
	}

	// Delete Notification
	$notifications = Notifications::where('author',$id)
	->orWhere('destination', $id)
	->get();

	if(isset( $notifications ) ){
		foreach($notifications as $notification){
			$notification->delete();
		}
	}

	// Images - Removed as Images and Stock models no longer exist
	// Image functionality was removed during conversion to universal starter kit
	// This section is commented out to prevent errors

	// User Reported
	$users_reporteds = UsersReported::where('user_id', '=', $id)->orWhere('id_reported', '=', $id)->get();

	if (isset($users_reporteds)) {
		foreach ($users_reporteds as $users_reported ) {
				$users_reported->delete();
			}// End
	}

	return redirect('panel/admin/members')->withSuccessMessage(trans('admin.success_delete'));

	}//<--- End Method

	// Admin Credit/Debit User Balance
	public function creditUser(Request $request)
	{
		\Log::info('CREDIT METHOD CALLED', [
			'request_data' => $request->all(),
			'user_agent' => $request->userAgent(),
			'ip' => $request->ip()
		]);

		try {
			$request->validate([
				'user_id' => 'required|exists:users,id',
				'amount' => 'required|numeric|min:0.01',
				'description' => 'nullable|string|max:1000'
			]);

			$user = User::findOrFail($request->user_id);
			$admin = auth()->user();

			\Log::info('CREDIT VALIDATION PASSED', [
				'user_id' => $user->id,
				'admin_id' => $admin->id,
				'amount' => $request->amount
			]);

			// Log transaction and update user balance
			$adminRole = $admin->role() ? $admin->role()->name : 'Admin';
			TransactionService::logAdminCredit(
				$user->id,
				$request->amount,
				'Admin Credit by ' . $adminRole . ($request->description ? ' - ' . $request->description : '')
			);

			\Log::info('CREDIT TRANSACTION COMPLETED', [
				'user_id' => $user->id,
				'amount' => $request->amount
			]);

			return back()->withSuccessMessage('Credit of Rs. ' . number_format($request->amount, 2) . ' added to ' . $user->username . ' successfully.');
		} catch (\Exception $e) {
			\Log::error('CREDIT METHOD ERROR', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			return back()->withErrorMessage('Error: ' . $e->getMessage());
		}
	}

	public function debitUser(Request $request)
	{
		\Log::info('DEBIT METHOD CALLED', [
			'request_data' => $request->all(),
			'user_agent' => $request->userAgent(),
			'ip' => $request->ip()
		]);

		try {
			$request->validate([
				'user_id' => 'required|exists:users,id',
				'amount' => 'required|numeric|min:0.01',
				'description' => 'nullable|string|max:1000'
			]);

			$user = User::findOrFail($request->user_id);
			$admin = auth()->user();

			\Log::info('DEBIT VALIDATION PASSED', [
				'user_id' => $user->id,
				'admin_id' => $admin->id,
				'amount' => $request->amount,
				'current_balance' => $user->balance
			]);

			// Check if user has sufficient balance
			if ($user->balance < $request->amount) {
				\Log::warning('DEBIT INSUFFICIENT BALANCE', [
					'user_id' => $user->id,
					'current_balance' => $user->balance,
					'requested_amount' => $request->amount
				]);
				return back()->withErrorMessage('Insufficient balance. User has Rs. ' . number_format($user->balance, 2) . ' but trying to deduct Rs. ' . number_format($request->amount, 2));
			}

			// Log transaction and update user balance
			$adminRole = $admin->role() ? $admin->role()->name : 'Admin';
			TransactionService::logAdminDebit(
				$user->id,
				$request->amount,
				'Admin Debit by ' . $adminRole . ($request->description ? ' - ' . $request->description : '')
			);

			\Log::info('DEBIT TRANSACTION COMPLETED', [
				'user_id' => $user->id,
				'amount' => $request->amount
			]);

			return back()->withSuccessMessage('Amount of Rs. ' . number_format($request->amount, 2) . ' deducted from ' . $user->username . ' successfully.');
		} catch (\Exception $e) {
			\Log::error('DEBIT METHOD ERROR', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			return back()->withErrorMessage('Error: ' . $e->getMessage());
		}
	}

	public function testMethod()
	{
		\Log::info('TEST METHOD CALLED - AdminUserController is working!');
		return response()->json([
			'success' => true,
			'message' => 'AdminUserController is working!',
			'timestamp' => now()
		]);
	}

}
