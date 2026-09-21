<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Plans;
use App\Models\Deposits;
use Laravel\Cashier\Cashier;
use App\Models\AdminSettings;
use Illuminate\Http\Response;
use App\Models\PaymentGateways;
use Laravel\Cashier\Subscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Http\Controllers\WebhookController;

class StripeWebHookController extends WebhookController
{
  use Traits\FunctionsTrait;

  /**
   *
   * customer.subscription.deleted
   *
   * @param array $payload
   * @return Response|\Symfony\Component\HttpFoundation\Response
   */
  public function handleCustomerSubscriptionDeleted(array $payload)
  {
    $user = $this->getUserByStripeId($payload['data']['object']['customer']);
    if ($user) {
      $user->subscriptions->filter(function ($subscription) use ($payload) {
        return $subscription->stripe_id === $payload['data']['object']['id'];
      })->each(function ($subscription) {
        $subscription->markAsCanceled();
      });
    }
    return new Response('Webhook Handled', 200);
  }

  /**
   *
   * WEBHOOK Insert the information of each payment in the Payments table when successfully generating an invoice in Stripe
   *
   * @param array $payload
   * @return Response|\Symfony\Component\HttpFoundation\Response
   */
  public function handleInvoicePaymentSucceeded($payload)
  {
    try {
      $settings = AdminSettings::first();
      $data     = $payload['data'];
      $object   = $data['object'];
      $customer = $object['customer'];
      $amount   = in_array(config('settings.currency_code'), config('currencies.zero_decimal')) ? $object['subtotal'] : ($object['subtotal'] / 100);
      $user     = $this->getUserByStripeId($customer);
      $interval = $object['lines']['data'][0]['metadata']['interval'] ?? 'month';
      $taxes    = $object['lines']['data'][0]['metadata']['taxes'] ?? null;

      if ($user) {
        $subscription = Subscription::whereStripeId($object['subscription'])->first();
        if ($subscription) {
          $subscription->stripe_status = "active";
          $subscription->interval = $interval;
          $subscription->payment_gateway = 'Stripe';

          // Get data Plan
          $plan = Plans::wherePlanId($subscription->stripe_price)->first();

          if ($object['billing_reason'] == 'subscription_create') {
            User::find($subscription->user_id)->update(['downloads' => $plan->downloads_per_month]);
          }

          // Renewal cycle
          if ($object['billing_reason'] == 'subscription_cycle') {
            if ($plan->unused_downloads_rollover) {
              User::find($subscription->user_id)->increment('downloads', $plan->downloads_per_month);
            } else {
              User::find($subscription->user_id)->update(['downloads' => $plan->downloads_per_month]);
            }
          }

          // Save subscription
          $subscription->save();

          // Create Invoice
          $this->invoiceSubscription($subscription->user_id, $subscription->id, $amount, $taxes, true);
        }
        return new Response('Webhook Handled: {handleInvoicePaymentSucceeded}', 200);
      }
      return new Response('Webhook Handled but user not found: {handleInvoicePaymentSucceeded}', 200);
    } catch (\Exception $exception) {
      Log::debug($exception->getMessage());
      return new Response('Webhook Unhandled: {handleInvoicePaymentSucceeded}', $exception->getCode());
    }
  }

  /**
   *
   * checkout.session.completed
   *
   * @param array $payload
   * @return Response|\Symfony\Component\HttpFoundation\Response
   */
  public function handleCheckoutSessionCompleted($payload)
  {
    try {
      $settings = AdminSettings::first();
      $data     = $payload['data'];
      $object   = $data['object'];
      $userId   = $object['metadata']['userId'] ?? null;
      $amount   = $object['metadata']['amount'] ?? null;

      // Buy data
      $token   = $object['metadata']['token'] ?? null;
      $license   = $object['metadata']['license'] ?? null;
      $type   = $object['metadata']['type'] ?? null;

      $taxes    = $object['metadata']['taxes'] ?? null;
      $mode     = $object['metadata']['mode'] ?? null;

      if (!isset($mode)) {
        return new Response('Webhook Handled with error: type transaction not defined', 500);
      }

      // Add funds (Deposit)
      if (isset($mode) && $mode == 'deposit') {
        if ($object['payment_status'] == 'paid' && isset($userId)) {
          $amount_total = $object['amount_total'] / 100;

          if (isset($amount) && $amount_total >= $amount) {

            // Check transaction
            $verifiedTxnId = Deposits::where('txn_id', $object['payment_intent'])->first();

            if (!$verifiedTxnId) {
              // Insert Deposit
              $this->deposit($userId, $object['payment_intent'], $amount, 'Stripe', $taxes);

              // Add Funds to User
              User::find($userId)->increment('funds', $amount);
            }
          }
        }
      } // End Add funds

      // Buy (Placeholder for future implementation)
      if (isset($mode) && $mode == 'sale') {
        // This section can be implemented for your specific product types
        Log::info('Sale transaction received but not implemented for this starter kit');
      } // End Buy

      return new Response('Webhook Handled: {handleCheckoutSessionCompleted}', 200);
    } catch (\Exception $exception) {
      Log::debug($exception->getMessage());
      return new Response('Webhook Unhandled: {handleCheckoutSessionCompleted}', $exception->getCode());
    }
  }

  /**
   *
   * charge.refunded
   *
   * @param array $payload
   * @return Response|\Symfony\Component\HttpFoundation\Response
   */
  public function handleChargeRefunded($payload)
  {
    try {
      $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
      $stripe->subscriptions->cancel($payload['data']['object']['subscription'], []);

      return new Response('Webhook Handled: {handleChargeRefunded}', 200);
    } catch (\Exception $exception) {
      Log::debug("Exception Webhook {handleChargeRefunded}: " . $exception->getMessage() . ", Line: " . $exception->getLine() . ', File: ' . $exception->getFile());
      return new Response('Webhook Handled with error: {handleChargeRefunded}', 400);
    }
  }

  /**
   * WEBHOOK Manage the SCA by notifying the user by email
   *
   * @param  array  $payload
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function handleInvoicePaymentActionRequired(array $payload)
  {
    $subscription = Subscription::whereStripeId($payload['data']['object']['subscription'])->first();
    if ($subscription) {
      $subscription->stripe_status = "incomplete";
      $subscription->last_payment = $payload['data']['object']['payment_intent'];
      $subscription->save();
    }

    if (is_null($notification = config('cashier.payment_notification'))) {
      return $this->successMethod();
    }

    if ($user = $this->getUserByStripeId($payload['data']['object']['customer'])) {
      if (in_array(Notifiable::class, class_uses_recursive($user))) {
        $payment = new \Laravel\Cashier\Payment(Cashier::stripe()->paymentIntents->retrieve(
          $payload['data']['object']['payment_intent']
        ));

        $user->notify(new $notification($payment));
      }
    }
    return $this->successMethod();
  }
}
