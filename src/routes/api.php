<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::apiResources([
        'files' => \Lyre\File\Http\Controllers\FileController::class,
    ]);

    Route::get('/files/stream/{slug}.{extension}', [\Lyre\File\Http\Controllers\FileController::class, 'stream'])->name('stream');
    Route::get('/files/download/{slug}.{extension}', [\Lyre\File\Http\Controllers\FileController::class, 'download'])->name('download');
});
