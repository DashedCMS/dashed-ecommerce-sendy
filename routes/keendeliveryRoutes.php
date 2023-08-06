<?php

use Illuminate\Support\Facades\Route;
use Dashed\DashedCore\Middleware\AdminMiddleware;
use Dashed\DashedEcommerceKeendelivery\Controllers\KeendeliveryController;

Route::middleware(['web', AdminMiddleware::class])->prefix(config('filament.path') . '/keendelivery')->group(function () {
    Route::get('/download-labels', [KeendeliveryController::class, 'downloadLabels'])->name('dashed.keendelivery.download-labels');
});
