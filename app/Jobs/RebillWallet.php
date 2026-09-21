<?php

namespace App\Jobs;

use App\Helper;
use App\Models\Plans;
use App\Models\TaxRates;
use App\Models\Subscriptions;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\Traits\FunctionsTrait;

class RebillWallet implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FunctionsTrait;

  public function handle(): void
  {
    $subscriptions = Subscriptions::where('ends_at', '<=', now())
      ->whereRebillWallet('on')
      ->whereCancelled('no')
      ->latest()
      ->whereIn('id', function ($q) {
        $q->selectRaw('MAX(id) FROM subscriptions GROUP BY user_id');
      })
      ->get();

    if ($subscriptions) {
      foreach ($subscriptions as $subscription) {
        // Get price of Plan
        $plan = Plans::wherePlanId($subscription->stripe_price)->first();

        // Get Taxes
        $taxes = TaxRates::whereIn('id', collect(explode('_', $subscription->taxes)))->get();
        $originalPlanPrice = $subscription->interval == 'month' ? $plan->price : $plan->price_year;
        $totalTaxes = ($originalPlanPrice * $taxes->sum('percentage') / 100);
        $planPrice = ($originalPlanPrice + $totalTaxes);

        if ($subscription->user()->funds >= $planPrice) {
          // Create Invoice
          $this->invoiceSubscription($subscription->user_id, $subscription->id, $originalPlanPrice, $subscription->taxes, true);

          // Subtract user funds
          $subscription->user()->decrement('funds', $planPrice);

          // Downloads per month
          if ($plan->unused_downloads_rollover) {
            $subscription->user()->increment('downloads', $plan->downloads_per_month);
          } else {
            $subscription->user()->update(['downloads' => $plan->downloads_per_month]);
          }

          $subscription->update([
            'ends_at' => Helper::planInterval($subscription->interval)
          ]);
        } else {
          // Remove downloads
          $subscription->user()->update(['downloads' => 0]);
        }
      }
    }
  }
}
