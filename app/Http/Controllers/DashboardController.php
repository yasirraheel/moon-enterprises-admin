<?php

namespace App\Http\Controllers;

use App\Helper;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Deposits;
use Illuminate\Http\Request;
use App\Models\PaymentGateways;

class DashboardController extends Controller
{

  public function __construct(Request $request)
  {
    $this->middleware('SellOption');
    $this->request = $request;
  }

  // Dashboard
  public function dashboard()
  {
    // For TTS application, we'll show user's credit information instead of sales
    $user = auth()->user();

    // Get user's current balance
    $userBalance = $user->balance ?? 0;

    // Initialize empty arrays for chart data (since we don't have sales data)
    $monthsData = [];
    $earningNetUserSum = [];
    $lastSales = [];

    // Generate dummy chart data for the last 30 days
    for ($i = 0; $i <= 30; ++$i) {
      $date = date('Y-m-d', strtotime('-' . $i . ' day'));
      $formatDate = Helper::formatDateChart($date);
      $monthsData[] = "'$formatDate'";
      $earningNetUserSum[] = 0; // No earnings data for TTS
      $lastSales[] = 0; // No sales data for TTS
    }

    // Set all revenue stats to 0 since this is a TTS application
    $stat_revenue_today = 0;
    $stat_revenue_yesterday = 0;
    $stat_revenue_week = 0;
    $stat_revenue_last_week = 0;
    $stat_revenue_month = 0;
    $stat_revenue_last_month = 0;
    $earningNetUser = 0;

    $label = implode(',', array_reverse($monthsData));
    $data = implode(',', array_reverse($earningNetUserSum));
    $datalastSales = implode(',', array_reverse($lastSales));

    $photosPending = 0; // Images functionality removed
    $totalImages = 0; // Images functionality removed
    $totalSales = 0; // Sales functionality removed

    return view('dashboard.dashboard', [
      'earningNetUser' => $earningNetUser,
      'label' => $label,
      'data' => $data,
      'datalastSales' => $datalastSales,
      'photosPending' => $photosPending,
      'totalImages' => $totalImages,
      'totalSales' => $totalSales,
      'stat_revenue_today' => $stat_revenue_today,
      'stat_revenue_yesterday' => $stat_revenue_yesterday,
      'stat_revenue_week' => $stat_revenue_week,
      'stat_revenue_last_week' => $stat_revenue_last_week,
      'stat_revenue_month' => $stat_revenue_month,
      'stat_revenue_last_month' => $stat_revenue_last_month,
      'userBalance' => $userBalance
    ]);
  } //<--- End Method

  public function photos()
  {
    // Images functionality removed for universal starter kit
    $data = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
      'path' => request()->url(),
    ]);

    return view('dashboard.photos', ['data' => $data, 'query' => null, 'sort' => null]);
  } //<--- End Method

  public function sales()
  {
    // Sales functionality removed for universal starter kit
    $data = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
      'path' => request()->url(),
    ]);

    return view('dashboard.sales')->withData($data);
  } //<--- End Method

  public function purchases()
  {
    // Purchases functionality removed for universal starter kit
    $data = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
      'path' => request()->url(),
    ]);

    return view('dashboard.purchases')->withData($data);
  } //<--- End Method

  public function deposits()
  {

    $data = Deposits::whereUserId(auth()->id())->orderBy('id', 'desc')->paginate(20);

    return view('dashboard.deposits-history')->withData($data);
  } //<--- End Method

  // Add Funds
  public function addFunds()
  {
    // Get Deposits History
    $data = Deposits::whereUserId(auth()->id())->orderBy('id', 'desc')->paginate(20);

    // Stripe Key
    $_stripe = PaymentGateways::where('id', 2)->where('enabled', '1')->select('key')->first();

    // Payments Gateways
    $paymentGateways = PaymentGateways::where('enabled', '1')->orderBy('type', 'DESC')->get();

    return view('dashboard.add-funds')->with([
      '_stripe' => $_stripe,
      'data' => $data,
      'paymentGateways' => $paymentGateways
    ]);
  } //<--- End Method


  public function downloads()
  {
    // Downloads functionality removed for universal starter kit
    $data = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, [
      'path' => request()->url(),
    ]);

    return view('dashboard.downloads')->withData($data);
  } //<--- End Method

}
