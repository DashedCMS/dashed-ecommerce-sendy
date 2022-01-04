<?php

use Illuminate\Support\Facades\Route;
use Qubiqx\QcommerceCore\Middleware\AdminMiddleware;
use Qubiqx\QcommerceEcommerceKeendelivery\Controllers\KeendeliveryController;

Route::middleware(['web', AdminMiddleware::class])->prefix(config('filament.path') . '/keendelivery')->group(function () {
    Route::get('/download-labels', [KeendeliveryController::class, 'downloadLabels'])->name('qcommerce.keendelivery.download-labels');
});
