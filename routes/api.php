<?php

use App\Http\Controllers\Api\Contact\CallContact;
use App\Http\Controllers\Api\Contact\ContactController;
use Illuminate\Support\Facades\Route;

Route::controller(ContactController::class)->group(function () {
    Route::get('/contacts', 'index');
    Route::get('/contacts/{uuid}', 'show')->whereUuid('uuid');
    Route::post('/contacts', 'store');
    Route::put('/contacts/{uuid}', 'update');
    Route::delete('/contacts/{uuid}', 'destroy')->whereUuid('uuid');
});

Route::post('/contacts/{uuid}/call', CallContact::class)->whereUuid('uuid');

