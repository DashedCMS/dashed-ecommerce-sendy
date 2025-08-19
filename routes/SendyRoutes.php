<?php

use Illuminate\Support\Facades\Route;
use Dashed\DashedCore\Middleware\AdminMiddleware;
use Dashed\DashedEcommerceSendy\Controllers\SendyController;

Route::middleware(['web', AdminMiddleware::class])->prefix('dashed/sendy')->group(function () {
    Route::get('/download-labels', [SendyController::class, 'downloadLabels'])->name('dashed.sendy.download-labels');
});
