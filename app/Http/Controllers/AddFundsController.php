<?php

namespace App\Http\Controllers;

use App\Models\Deposits;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\PaymentGateways;
use Illuminate\Support\Facades\Validator;
use App\Notifications\AdminDepositPending;
use Illuminate\Support\Facades\Notification;

class AddFundsController extends Controller
{
	use Traits\FunctionsTrait;

	public function __construct(AdminSettings $settings, Request $request)
	{
		$this->settings = $settings::first();
		$this->request = $request;
	}

	public function send()
	{
		if ($this->settings->sell_option == 'off') {
			return response()->json([
				'success' => false,
				'errors' => ['error' => __('misc.error')],
			]);
		}

		if ($this->settings->currency_position == 'right') {
			$currencyPosition =  2;
		} else {
			$currencyPosition =  null;
		}

		// Get name of Payment Gateway
		$payment = PaymentGateways::find($this->request->payment_gateway);
		$this->request['image_screenshot'] = $payment->type == 'bank' ? true : false;

		Validator::extend('check_payment_gateway', function ($attribute, $value, $parameters) {
			return PaymentGateways::find($value);
		});

		$messages = array(
			'amount.min' => __('misc.amount_minimum' . $currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
			'amount.max' => __('misc.amount_maximum' . $currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
			'payment_gateway.check_payment_gateway' => __('misc.payments_error'),
			'image.required_if' => __('misc.please_select_image'),
		);

		//<---- Validation
		$validator = Validator::make($this->request->all(), [
			'amount' => 'required|integer|min:' . $this->settings->min_deposits_amount . '|max:' . $this->settings->max_deposits_amount,
			'payment_gateway' => 'required|check_payment_gateway',
			'image' => 'required_if:image_screenshot,==,true|image|max:1048576',
		], $messages);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'errors' => $validator->getMessageBag()->toArray(),
			]);
		}

		if (!$payment) {
			return response()->json([
				'success' => false,
				'errors' => ['error' => __('misc.payments_error')],
			]);
		}

		$paymentName = str_slug($payment->name);

		if ($payment->type == 'bank') {
			return $this->initBankTransfer();
		}

		// Send data to the payment processor
		return redirect()->route($paymentName, $this->request->except(['_token']));
	}

	public function initBankTransfer()
	{
		// Path Image
		$path = config('path.admin');

		if ($this->request->hasFile('image')) {
			$extension = $this->request->file('image')->getClientOriginalExtension();
			$fileImage = 'bt_' . strtolower(auth()->id() . time() . str_random(40) . '.' . $extension);

			$this->request->file('image')->storePubliclyAs($path, $fileImage);
		}

		$payment = PaymentGateways::whereName('Bank')->firstOrFail();
		$paymentFee = $payment->fee;
		$paymentFeeCents = $payment->fee_cents;

		$userId = auth()->id();
		$txnId = 'bt_'.str_random(25);
		$amount = $this->request->amount;
		$taxes = config('settings.tax_on_wallet') ? auth()->user()->taxesPayable() : null;
		$status = 'pending';

		// Percentage applied
		$percentageApplied =  $paymentFeeCents == 0.00 ? $paymentFee . '%' : $paymentFee . '%' . ' + ' . $paymentFeeCents;

		// Percentage applied amount
		$transactionFeeAmount = number_format($amount + ($amount * $paymentFee / 100) + $paymentFeeCents, 2, '.', '');
		$transactionFee = ($transactionFeeAmount - $amount);

		$deposit = new Deposits();
		$deposit->user_id = $userId;
		$deposit->txn_id = $txnId;
		$deposit->amount = $amount;
		$deposit->payment_gateway = 'Bank';
		$deposit->status = $status;
		$deposit->screenshot_transfer = $fileImage;
		$deposit->save();

		$this->invoiceDeposits($userId, $deposit->id, $amount, $percentageApplied, $transactionFee, $taxes, $status);

		// Notify Admin via Email
		try {
			Notification::route('mail', $this->settings->email_admin)
				->notify(new AdminDepositPending($deposit));
		} catch (\Exception $e) {
			\Log::info('Error AdminDepositPending - ' . $e->getMessage());
		}

		// Notify User via App (API Notification)
		try {
			\App\Services\NotificationService::notifyDepositRequested($userId, $amount, $deposit->id);
		} catch (\Exception $e) {
			\Log::info('Error notifyDepositRequested - ' . $e->getMessage());
		}

		return response()->json([
			"success" => true,
			"status" => 'pending',
			'status_info' => __('misc.pending_deposit')
		]);
	}
}
