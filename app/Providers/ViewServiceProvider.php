<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Plans;
use App\Models\Deposits;
use App\Models\Withdrawals;
use App\Models\TaxRates;
use App\Models\Languages;
use App\Models\Categories;
use App\Models\AdminSettings;
use App\Models\UsersReported;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 */
	public function register(): void
	{
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot()
	{
		try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
			return false;
        }

		$settings = AdminSettings::first();
		$categoriesCount = 0;
		$categoriesMain = collect();
		$categories = collect();
		$languages = collect();
		
		try {
			$categoriesCount = Categories::count();
			$categoriesMain = Categories::where('mode', 'on')->orderBy('name')->take(5)->get();
			$categories = Categories::orderBy('name')->get();
			$languages = Languages::orderBy('name')->get();
		} catch (\Exception $e) {
			// If tables don't exist yet, use empty collections
			\Log::warning('ViewServiceProvider: Some tables may not exist yet: ' . $e->getMessage());
		}
		$taxRatesCount = TaxRates::whereStatus('1')->count();
		$userCount = User::whereStatus('active')->count();
		$plansActive = Plans::whereStatus('1')->count();
		$depositsPendingCount = Deposits::whereStatus('pending')
			->whereHas('user', function ($q) {
				$q->where('dealer_status', '!=', 'approved')->orWhereNull('dealer_status');
			})
			->count();

		$withdrawalsPendingCount = \App\Models\Withdrawals::whereStatus('pending')
			->whereHas('user', function ($q) {
				$q->where('dealer_status', '!=', 'approved')->orWhereNull('dealer_status');
			})
			->count();

		$dealerDepositsPendingCount = Deposits::whereStatus('pending')
			->whereHas('user', function ($q) {
				$q->where('dealer_status', 'approved');
			})
			->count();

		$dealerWithdrawalsPendingCount = \App\Models\Withdrawals::whereStatus('pending')
			->whereHas('user', function ($q) {
				$q->where('dealer_status', 'approved');
			})
			->count();

		$usersReported = UsersReported::selectRaw('COUNT(id) as total')->pluck('total')->first();

		// Universal starter kit - set default values for removed stock photo functionality
		$downloadsCount = 0;
		$imagesCount = 0;

		view()->share(compact(
			'settings',
			'categoriesCount',
			'categoriesMain',
			'categories',
			'languages',
			'taxRatesCount',
			'userCount',
			'plansActive',
			'depositsPendingCount',
			'withdrawalsPendingCount',
			'dealerDepositsPendingCount',
			'dealerWithdrawalsPendingCount',
			'usersReported',
			'downloadsCount',
			'imagesCount'
		));
	}
}
