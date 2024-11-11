<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboundCallController;
use App\Http\Controllers\FailedCallController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/api/inbound', [InboundCallController::class, 'handleInbound']);
Route::post('/api/fail', [FailedCallController::class, 'handleFail']);