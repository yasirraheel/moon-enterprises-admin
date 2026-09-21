<?php

namespace App\Http\Controllers;

use App\Helper;
// Images model removed for universal starter kit
use Illuminate\Http\Request;
use App\Models\PaymentGateways;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
	use Traits\FunctionsTrait;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function send()
	{
		if (config('settings.sell_option') == 'off') {
			return response()->json([
				'success' => false,
				'errors' => ['error' => __('misc.purchase_not_allowed')],
			]);
		}

		// Images functionality removed for universal starter kit
		// For now, we'll allow checkout for any token
		if (empty($this->request->token)) {
			return response()->json([
				'success' => false,
				'errors' => ['error' => __('misc.purchase_not_allowed')],
			]);
		}

		$messages = [
			'type.in' => __('misc.error'),
			'license.in' => __('misc.error')
		];

		//<---- Validation
		$validator = Validator::make($this->request->all(), [
			'type' => 'required|in:small,medium,large,vector',
			'license' => 'required|in:regular,extended',
			'payment_gateway' => 'required',
		], $messages);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'errors' => $validator->getMessageBag()->toArray(),
			]);
		}

		// Wallet
		if ($this->request->payment_gateway == 'wallet') {
			return $this->sendWallet();
		}

		// Get name of Payment Gateway
		$payment = PaymentGateways::find($this->request->payment_gateway);

		if (!$payment) {
			return response()->json([
				'success' => false,
				'errors' => ['error' => __('misc.payments_error')],
			]);
		}

		$routePayment = str_slug($payment->name) . '.buy';

		// Send data to the payment processor
		return redirect()->route($routePayment, $this->request->except(['_token']));
	}

	private function sendWallet()
	{
		// Images functionality removed for universal starter kit
		return response()->json([
			"success" => false,
			"errors" => ['error' => __('misc.purchase_not_allowed')]
		]);
	}
}
