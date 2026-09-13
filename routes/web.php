<?php

use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\PublicAssetController;
use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.login'));

Route::get('/aset/{asset}', [PublicAssetController::class, 'show'])
    ->name('assets.public');

Route::get('/aset/{asset}/qr.svg', [QrCodeController::class, 'svg'])
    ->name('assets.qr');

Route::get('/aset/{asset}/laporkan', [PublicAssetController::class, 'report'])
    ->name('assets.report');

Route::post('/aset/{asset}/laporkan', [PublicAssetController::class, 'storeReport'])
    ->middleware('throttle:10,1')
    ->name('assets.report.store');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/masuk', [AuthController::class, 'create'])->name('login');
        Route::post('/masuk', [AuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function (): void {
        Route::post('/keluar', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('/aset/{asset}/qr.png', [QrCodeController::class, 'png'])
            ->name('assets.qr.png');

        Route::get('/aset/{asset}/label', [AssetController::class, 'label'])
            ->name('assets.label');

        Route::resource('assets', AssetController::class);

        Route::get('/laporan', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/laporan/{report}', [ReportController::class, 'show'])
            ->name('reports.show');

        Route::patch('/laporan/{report}/setujui', [ReportController::class, 'approve'])
            ->name('reports.approve');

        Route::patch('/laporan/{report}/tolak', [ReportController::class, 'reject'])
            ->name('reports.reject');
    });
});
