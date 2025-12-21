<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ScooterController;
use App\Http\Controllers\Admin\GeoZoneController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('scooters', ScooterController::class);
            Route::get('scooters-api/map-data', [ScooterController::class, 'getMapData'])->name('scooters.map-data');
            Route::post('scooters/{scooter}/lock', [ScooterController::class, 'lock'])->name('scooters.lock');
            Route::post('scooters/{scooter}/unlock', [ScooterController::class, 'unlock'])->name('scooters.unlock');
            Route::get('scooters/{scooter}/lock-status', [ScooterController::class, 'getLockStatus'])->name('scooters.lock-status');
            
            // User routes - must be before resource to avoid conflicts
            Route::get('users/inactive', [\App\Http\Controllers\Admin\UserController::class, 'inactive'])->name('users.inactive');
            Route::get('users/active', [\App\Http\Controllers\Admin\UserController::class, 'active'])->name('users.active');
            Route::post('users/bulk-activate', [\App\Http\Controllers\Admin\UserController::class, 'bulkActivate'])->name('users.bulk-activate');
            Route::get('users/{user}/quick-preview', [\App\Http\Controllers\Admin\UserController::class, 'quickPreview'])->name('users.quick-preview');
            Route::get('users/{user}/review-notes', [\App\Http\Controllers\Admin\UserController::class, 'getReviewNotes'])->name('users.review-notes.get');
            Route::patch('users/{user}/review-notes', [\App\Http\Controllers\Admin\UserController::class, 'updateReviewNotes'])->name('users.review-notes.update');
            
            Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
            Route::post('users/{user}/toggle-active', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');
            Route::post('users/{user}/add-wallet-balance', [\App\Http\Controllers\Admin\UserController::class, 'addWalletBalance'])->name('users.add-wallet-balance');
            Route::post('users/{user}/add-loyalty-points', [\App\Http\Controllers\Admin\UserController::class, 'addLoyaltyPoints'])->name('users.add-loyalty-points');
            
            Route::resource('trips', \App\Http\Controllers\Admin\TripController::class);
            Route::post('trips/{trip}/complete', [\App\Http\Controllers\Admin\TripController::class, 'complete'])->name('trips.complete');
            Route::post('trips/{trip}/cancel', [\App\Http\Controllers\Admin\TripController::class, 'cancel'])->name('trips.cancel');

            Route::resource('geo-zones', GeoZoneController::class);
            
            Route::resource('penalties', \App\Http\Controllers\Admin\PenaltyController::class);
            Route::post('penalties/{penalty}/mark-as-paid', [\App\Http\Controllers\Admin\PenaltyController::class, 'markAsPaid'])->name('penalties.mark-as-paid');
            Route::post('penalties/{penalty}/waive', [\App\Http\Controllers\Admin\PenaltyController::class, 'waive'])->name('penalties.waive');
            
            Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class);
            
            Route::resource('subscriptions', \App\Http\Controllers\Admin\SubscriptionController::class);
            Route::post('subscriptions/{subscription}/renew', [\App\Http\Controllers\Admin\SubscriptionController::class, 'renew'])->name('subscriptions.renew');
            Route::post('subscriptions/{subscription}/cancel', [\App\Http\Controllers\Admin\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
            Route::post('subscriptions/{subscription}/suspend', [\App\Http\Controllers\Admin\SubscriptionController::class, 'suspend'])->name('subscriptions.suspend');
            Route::post('subscriptions/{subscription}/activate', [\App\Http\Controllers\Admin\SubscriptionController::class, 'activate'])->name('subscriptions.activate');
            
            Route::get('loyalty', [\App\Http\Controllers\Admin\LoyaltyController::class, 'index'])->name('loyalty.index');
            Route::get('loyalty/settings', [\App\Http\Controllers\Admin\LoyaltyController::class, 'settings'])->name('loyalty.settings');
            Route::post('loyalty/settings', [\App\Http\Controllers\Admin\LoyaltyController::class, 'updateSettings'])->name('loyalty.settings.update');
            Route::post('users/{user}/loyalty/add-points', [\App\Http\Controllers\Admin\LoyaltyController::class, 'addPoints'])->name('users.loyalty.add-points');
            Route::post('users/{user}/loyalty/deduct-points', [\App\Http\Controllers\Admin\LoyaltyController::class, 'deductPoints'])->name('users.loyalty.deduct-points');
            
            Route::get('scooter-logs', [\App\Http\Controllers\Admin\ScooterLogController::class, 'index'])->name('scooter-logs.index');
            Route::get('scooter-logs/critical', [\App\Http\Controllers\Admin\ScooterLogController::class, 'getCriticalAlerts'])->name('scooter-logs.critical');
            Route::get('scooter-logs/{scooterLog}', [\App\Http\Controllers\Admin\ScooterLogController::class, 'show'])->name('scooter-logs.show');
            Route::post('scooter-logs/{scooterLog}/resolve', [\App\Http\Controllers\Admin\ScooterLogController::class, 'markAsResolved'])->name('scooter-logs.resolve');
            
            Route::resource('maintenance', \App\Http\Controllers\Admin\MaintenanceController::class);
            Route::post('maintenance/{maintenance}/start', [\App\Http\Controllers\Admin\MaintenanceController::class, 'start'])->name('maintenance.start');
            Route::post('maintenance/{maintenance}/complete', [\App\Http\Controllers\Admin\MaintenanceController::class, 'complete'])->name('maintenance.complete');
            Route::post('maintenance/{maintenance}/cancel', [\App\Http\Controllers\Admin\MaintenanceController::class, 'cancel'])->name('maintenance.cancel');
            
            Route::get('wallet', [\App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallet.index');
            Route::get('wallet/{walletTransaction}', [\App\Http\Controllers\Admin\WalletController::class, 'show'])->name('wallet.show');
            Route::post('wallet/create-transaction', [\App\Http\Controllers\Admin\WalletController::class, 'createTransaction'])->name('wallet.create-transaction');
            Route::get('wallet/paymob/payment', [\App\Http\Controllers\Admin\WalletController::class, 'paymobPayment'])->name('wallet.paymob.payment');
            Route::post('wallet/paymob/callback', [\App\Http\Controllers\Admin\WalletController::class, 'paymobCallback'])->name('wallet.paymob.callback');
            Route::get('wallet/paymob/return', [\App\Http\Controllers\Admin\WalletController::class, 'paymobReturn'])->name('wallet.paymob.return');
            Route::post('users/{user}/wallet/top-up', [\App\Http\Controllers\Admin\WalletController::class, 'topUp'])->name('users.wallet.top-up');
            Route::post('users/{user}/wallet/refund', [\App\Http\Controllers\Admin\WalletController::class, 'refund'])->name('users.wallet.refund');
            Route::post('users/{user}/wallet/adjust', [\App\Http\Controllers\Admin\WalletController::class, 'adjust'])->name('users.wallet.adjust');
            Route::get('users/{user}/wallet/transactions', [\App\Http\Controllers\Admin\WalletController::class, 'userTransactions'])->name('users.wallet.transactions');
            
            Route::get('reports', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('reports.index');
            Route::get('reports/overview', [\App\Http\Controllers\Admin\ReportsController::class, 'overview'])->name('reports.overview');
            Route::get('reports/trips', [\App\Http\Controllers\Admin\ReportsController::class, 'trips'])->name('reports.trips');
            Route::get('reports/revenue', [\App\Http\Controllers\Admin\ReportsController::class, 'revenue'])->name('reports.revenue');
            Route::get('reports/users', [\App\Http\Controllers\Admin\ReportsController::class, 'users'])->name('reports.users');
            Route::get('reports/scooters', [\App\Http\Controllers\Admin\ReportsController::class, 'scooters'])->name('reports.scooters');
            Route::get('reports/maintenance', [\App\Http\Controllers\Admin\ReportsController::class, 'maintenance'])->name('reports.maintenance');
            Route::get('reports/wallet', [\App\Http\Controllers\Admin\ReportsController::class, 'wallet'])->name('reports.wallet');
            
            Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
            Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
            Route::post('users/{user}/assign-roles', [\App\Http\Controllers\Admin\UserController::class, 'assignRoles'])->name('users.assign-roles');
            
            Route::get('payment-settings', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'index'])->name('payment-settings.index');
            Route::post('payment-settings', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'update'])->name('payment-settings.update');
            Route::post('payment-settings/test-connection', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'testConnection'])->name('payment-settings.test-connection');
        });
});

require __DIR__.'/auth.php';
