<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\User;
use App\Models\Plans;
use App\Models\Deposits;
use App\Models\Purchases;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\Subscriptions;
use App\Models\PaymentGateways;
use GuzzleHttp\Client as HttpClient;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController extends Controller
{
  use Traits\FunctionsTrait;

  public function __construct(AdminSettings $settings, Request $request)
  {
    $this->settings = $settings::first();
    $this->request = $request;
  }

  public function show()
  {
    if (!$this->request->expectsJson()) {
      abort(404);
    }

    // Get Payment Gateway
    $payment = PaymentGateways::findOrFail($this->request->payment_gateway);

    $feePayPal   = $payment->fee;
    $centsPayPal =  $payment->fee_cents;

    $taxes = $this->settings->tax_on_wallet ? ($this->request->amount * auth()->user()->isTaxable()->sum('percentage') / 100) : 0;

    $amountFixed = number_format($this->request->amount + ($this->request->amount * $feePayPal / 100) + $centsPayPal + $taxes, 2, '.', '');

    try {

      $urlSuccess = route('paypal.verify');
      $urlCancel  = url('user/dashboard/add/funds');

      $provider = new PayPalClient();

      $token = $provider->getAccessToken();
      $provider->setAccessToken($token);
      $order = $provider->createOrder([
        "intent" => "CAPTURE",
        'application_context' =>
        [
          'return_url' => $urlSuccess,
          'cancel_url' => $urlCancel,
          'shipping_preference' => 'NO_SHIPPING'
        ],

        "purchase_units" => [
          [
            "amount" => [
              "currency_code" => config('settings.currency_code'),
              "value" => $amountFixed,
              'breakdown' => [
                'item_total' => [
                  "currency_code" => config('settings.currency_code'),
                  "value" => $amountFixed
                ],
              ],
            ],
            'description' => __('misc.add_funds_desc'),

            'items' => [
              [
                'name' => __('misc.add_funds_desc'),
                'category' => 'DIGITAL_GOODS',
                'quantity' => '1',
                'unit_amount' => [
                  "currency_code" => config('settings.currency_code'),
                  "value" => $amountFixed
                ],
              ],
            ],

            'custom_id' => http_build_query([
              'id' => auth()->id(),
              'amount' => $this->request->amount,
              'tax' => $this->settings->tax_on_wallet ? auth()->user()->taxesPayable() : null,
              'mode' => 'deposit'
            ]),
          ],
        ],
      ]);

      return response()->json([
        'success' => true,
        'url' => $order['links'][1]['href']
      ]);
    } catch (\Exception $e) {

      \Log::debug($order['details']['issue']);

      return response()->json([
        'errors' => ['error' => $e->getMessage()]
      ]);
    }
  }

  // Buy photo
  public function buy()
  {
    if (!$this->request->expectsJson()) {
      abort(404);
    }
    try {

      // Get Payment Gateway
      PaymentGateways::whereId($this->request->payment_gateway)->whereName('PayPal')->firstOrFail();

      // Get Image
      $image = Images::where('token_id', $this->request->token)->firstOrFail();

      $priceItem = $this->settings->default_price_photos ?: $image->price;

      $itemPrice = $this->priceItem($this->request->license, $priceItem, $this->request->type);

      $itemName = __('misc.' . $this->request->type . '_photo') . ' - ' . __('misc.license_' . $this->request->license);

      $urlSuccess = route('paypal.verify');
      $urlCancel  = url('user/dashboard/purchases');

      $provider = new PayPalClient();

      $token = $provider->getAccessToken();
      $provider->setAccessToken($token);
      $order = $provider->createOrder([
        "intent" => "CAPTURE",
        'application_context' =>
        [
          'return_url' => $urlSuccess,
          'cancel_url' => $urlCancel,
          'shipping_preference' => 'NO_SHIPPING'
        ],

        "purchase_units" => [
          [
            "amount" => [
              "currency_code" => config('settings.currency_code'),
              "value" => Helper::amountGross($itemPrice),
              'breakdown' => [
                'item_total' => [
                  "currency_code" => config('settings.currency_code'),
                  "value" => Helper::amountGross($itemPrice)
                ],
              ],
            ],
            'description' => $itemName,

            'items' => [
              [
                'name' => $itemName,
                'category' => 'DIGITAL_GOODS',
                'quantity' => '1',
                'unit_amount' => [
                  "currency_code" => config('settings.currency_code'),
                  "value" => Helper::amountGross($itemPrice)
                ],
              ],
            ],

            'custom_id' => http_build_query([
              'id' => $image->id,
              'user' => auth()->id(),
              'type' => $this->request->type,
              'license' => $this->request->license,
              'tax' => auth()->user()->taxesPayable() ?? null,
              'mode' => 'sale'
            ]),
          ],
        ],
      ]);

      return response()->json([
        'success' => true,
        'url' => $order['links'][1]['href']
      ]);
    } catch (\Exception $e) {

      return response()->json([
        'errors' => ['error' => $e->getMessage()]
      ]);
    }
  }

  public function verifyTransaction()
  {
    // Get Payment Data
    $payment = PaymentGateways::whereName('PayPal')->first();

    // Init PayPal
    $provider = new PayPalClient();
    $token = $provider->getAccessToken();
    $provider->setAccessToken($token);

    try {
      // Get PaymentOrder using our transaction ID
      $order = $provider->capturePaymentOrder($this->request->token);
      $txnId = $order['purchase_units'][0]['payments']['captures'][0]['id'];

      // Parse the custom data parameters
      parse_str($order['purchase_units'][0]['payments']['captures'][0]['custom_id'] ?? null, $data);

      if ($order['status'] && $order['status'] === "COMPLETED") {
        if ($data) {
          switch ($data['mode']) {
              //============ Start Deposit ==============
            case 'deposit':

              // Check outh POST variable and insert in DB
              $verifiedTxnId = Deposits::where('txn_id', $txnId)->first();

              if (!isset($verifiedTxnId)) {

                // Insert Deposit status 'Pending'
                $this->deposit(
                  $data['id'],
                  $txnId,
                  $data['amount'],
                  'PayPal',
                  $data['tax'] ?? null
                );
                // Add Funds to User
                User::find($data['id'])->increment('funds', $data['amount']);
              } // <--- Verified Txn ID

              return redirect('user/dashboard/add/funds');

              break;

              //============ Start Sale ==============
            case 'sale':

              // Get Image
              $image = Images::whereId($data['id'])->firstOrFail();

              $priceItem = $this->settings->default_price_photos ?: $image->price;

              $itemPrice = $this->priceItem($data['license'], $priceItem, $data['type']);

              // Admin and user earnings calculation
              $earnings = $this->earningsAdminUser($image->user()->author_exclusive, $itemPrice, $payment->fee, $payment->fee_cents);

              // Check outh POST variable and insert in DB
              $verifiedTxnId = Purchases::where('txn_id', $txnId)->first();

              if (!isset($verifiedTxnId)) {
                $this->purchase(
                  $txnId,
                  $image,
                  $data['user'],
                  $itemPrice,
                  $earnings['user'],
                  $earnings['admin'],
                  $data['type'],
                  $data['license'],
                  $earnings['percentageApplied'],
                  'PayPal',
                  $data['tax'] ?? null
                );
              }

              return redirect('user/dashboard/purchases');

              break;
          } // Switch case
        } // data

        return redirect('/');
      }
    } catch (\Exception $e) {
      \Log::debug($e);

      return redirect('/');
    }
  }

  public function subscription()
  {
    $plan = Plans::wherePlanId($this->request->plan)->whereStatus('1')->firstOrFail();

    // Check Subscription
    if (auth()->user()->getSubscription()) {
      return response()->json([
        'success' => false,
        'errors' => ['error' => __('misc.subscription_exists')],
      ]);
    }

    // Get Payment Gateway
    PaymentGateways::whereName('PayPal')->whereEnabled(1)->firstOrFail();

    $urlSuccess = route('success.subscription', ['alert' => 'payment']);
    $urlCancel   = url('pricing');

    switch ($this->request->interval) {
      case 'month':
        $interval = 'MONTH';
        $interval_count = 1;
        break;

      case 'year':
        $interval = 'YEAR';
        $interval_count = 1;
        break;
    }

    // Init PayPal
    $provider = new PayPalClient();
    $token = $provider->getAccessToken();
    $provider->setAccessToken($token);

    $product_id = 'product_' . $plan->plan_id;

    try {
      // Get Product Details
      $product = $provider->showProductDetails($product_id);

      if (!isset($product['id'])) {
        // Create Product
        $requestId = 'create-product-' . time();

        $product = $provider->createProduct([
            'id' => $product_id,
            'name' => __('misc.subscription_plan', ['name' => $plan->name]),
            'description' => __('misc.subscription_plan', ['name' => $plan->name]),
            'type' => 'DIGITAL',
            'category' => 'DIGITAL_MEDIA_BOOKS_MOVIES_MUSIC',
          ], $requestId);
        }

    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'errors' => ['error' => $e->getMessage()]
      ]);
    }


    try {
      // Create Plan
      $planPayPal = 'plan_' . $plan->plan_id;

      $requestIdPlan = 'create-plan-' . time();

      $paypalPlan = $provider->createPlan([
        'product_id' => $product['id'],
        'name' => $planPayPal,
        'status' => 'ACTIVE',
        'billing_cycles' => [
          [
            'frequency' => [
              'interval_unit' => $interval,
              'interval_count' => $interval_count,
            ],
            'tenure_type' => 'REGULAR',
            'sequence' => 1,
            'total_cycles' => 0,
            'pricing_scheme' => [
              'fixed_price' => [
                'value' => Helper::amountGross($plan->price),
                'currency_code' => config('settings.currency_code'),
              ],
            ]
          ]
        ],
        'payment_preferences' => [
          'auto_bill_outstanding' => true,
          'payment_failure_threshold' => 0,
        ],
      ], $requestIdPlan);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'errors' => ['error' => $e->getMessage()]
      ]);
    }

    try {
      // Create Subscription
      $subscription = $provider->createSubscription([
        'plan_id' => $paypalPlan['id'],
        'application_context' => [
          'brand_name' => config('settings.title'),
          'locale' => 'en-US',
          'shipping_preference' => 'SET_PROVIDED_ADDRESS',
          'user_action' => 'SUBSCRIBE_NOW',
          'payment_method' => [
            'payer_selected' => 'PAYPAL',
            'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
          ],
          'return_url' => $urlSuccess,
          'cancel_url' => $urlCancel
        ],
        'custom_id' => http_build_query([
          'subscriber' => auth()->id(),
          'planId' => $plan->id,
          'interval' => $this->request->interval,
          'taxes' => auth()->user()->taxesPayable(),
        ])
      ]);

      return response()->json([
        'success' => true,
        'url' => $subscription['links'][0]['href']
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'errors' => ['error' => $e->getMessage()]
      ]);
    }
  }

  public function webhook()
  {
    // Get Payment Data
    $payment = PaymentGateways::whereName('PayPal')->first();

    // Init PayPal
    $provider = new PayPalClient();
    $token = $provider->getAccessToken();
    $provider->setAccessToken($token);

    $httpClient = new HttpClient();

    $baseUrl = 'https://' . ($payment->sandbox == 'true' ? 'api-m.sandbox' : 'api-m') . '.paypal.com/';

    // PayPal Webhook ID
    $webhookId = $payment->webhook_secret;

    // Get the payload's content
    $payload = $this->request->all();

    // Get payload's content verify Webhook
    $payloadWebhook = json_decode($this->request->getContent());

    // Verify the webhook signature
    try {
      $verifyWebHookSignatureRequest = $httpClient->request(
        'POST',
        $baseUrl . 'v1/notifications/verify-webhook-signature',
        [
          'headers' => [
            'Authorization' => 'Bearer ' . $token['access_token'],
            'Content-Type' => 'application/json'
          ],
          'body' => json_encode([
            'auth_algo' => $this->request->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => $this->request->header('PAYPAL-CERT-URL'),
            'transmission_id' => $this->request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => $this->request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $this->request->header('PAYPAL-TRANSMISSION-TIME'),
            'webhook_id' => $webhookId,
            'webhook_event' => $payloadWebhook
          ])
        ]
      );

      $verifyWebHookSignature = json_decode($verifyWebHookSignatureRequest->getBody()->getContents());
    } catch (\Exception $e) {
      Log::debug($e);

      return response()->json([
        'status' => 400
      ], 400);
    }

    // Check if the webhook's signature status is successful
    if ($verifyWebHookSignature->verification_status != 'SUCCESS') {
      Log::info('PayPal signature validation failed!');

      return response()->json([
        'status' => 400
      ], 400);
    }

    // Parse the custom data parameters
    parse_str($payload['resource']['custom_id'] ?? ($payload['resource']['custom'] ?? null), $data);

    if ($data) {
      if ($payload['event_type'] == 'PAYMENT.SALE.COMPLETED') {
        if (array_key_exists('billing_agreement_id', $payload['resource']) && !empty($payload['resource']['billing_agreement_id'])) {
          // Get user data
          $subscriber = User::find($data['subscriber']);

          // Check if Plan exists
          $plan = Plans::find($data['planId']);

          // Subscription ID
          $subscriptionId = $payload['resource']['billing_agreement_id'];

          // Get Subscription
          $subscription = Subscriptions::where('paypal_id', $subscriptionId)->first();

          // Taxes
          $taxes = $data['taxes'] ?? null;

          // Update date if subscription exists
          if ($subscription && $subscription->cancelled == 'no') {
            $subscription->ends_at = Helper::planInterval($data['interval']);
            $subscription->save();

            if ($plan->unused_downloads_rollover) {
              $subscriber->increment('downloads', $plan->downloads_per_month);
            } else {
              $subscriber->update(['downloads' => $plan->downloads_per_month]);
            }
          }

          // Insert if the subscription does not exist
          if (!$subscription) {
            // Insert DB
            $subscription = new Subscriptions();
            $subscription->user_id = $data['subscriber'];
            $subscription->stripe_price = $plan->plan_id;
            $subscription->paypal_id = $subscriptionId;
            $subscription->interval = $data['interval'];
            $subscription->ends_at = Helper::planInterval($data['interval']);
            $subscription->taxes = $taxes ?? null;
		        $subscription->payment_gateway = 'PayPal';
            $subscription->save();

            // Add downloads to user
            $subscriber->update(['downloads' => $plan->downloads_per_month]);
          }

          // Create Invoice
          $this->invoiceSubscription($subscription->user_id, $subscription->id, $plan->price, $taxes, true);

        }
      } // Payment Sale Completed
    } // $data custom id

    if ($payload['event_type'] == 'BILLING.SUBSCRIPTION.CANCELLED'
      || $payload['event_type'] == 'BILLING.SUBSCRIPTION.EXPIRED'
      || $payload['event_type'] == 'BILLING.SUBSCRIPTION.SUSPENDED'
    ) {
      $subscription = Subscriptions::where('paypal_id', $payload['resource']['id'])->first();

      if ($subscription) {
        $subscription->cancelled = 'yes';
        $subscription->save();
      }
    }

    if ($payload['event_type'] == 'PAYMENT.SALE.REFUNDED') {
      // Get Custom ID
      if ($data) {
        if (array_key_exists('id', $payload['resource']) && !empty($payload['resource']['id'])) {
          $subscription = Subscriptions::where('paypal_id', $payload['resource']['id'])->first();
          $subscription->cancelled = 'yes';
          $subscription->save();
        }
      }
    }
  }

  public function cancelSubscription($id)
  {
    $subscription = Subscriptions::whereId($id)->firstOrFail();

    // Init PayPal
    $provider = new PayPalClient();
    $token = $provider->getAccessToken();
    $provider->setAccessToken($token);

    try {
      $provider->cancelSubscription($subscription->subscription_id, 'Not satisfied with the service');

      $subscription->cancelled = 'yes';
      $subscription->save();
    } catch (\Exception) {
    }

    // Wait for the Webhook capture
    sleep(3);

    return back()->withSubscriptionCancel(__('misc.subscription_cancel'));
  }
}
