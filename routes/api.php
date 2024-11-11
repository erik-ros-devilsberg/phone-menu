<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboundCallController;
use App\Http\Controllers\FailedCallController;

Route::post('/inbound', [InboundCallController::class, 'handleInbound']);
Route::post('/fail', [FailedCallController::class, 'handleFail']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



// Place this at the end of your routes file
Route::fallback(function () {
    // Return a view or a response
    return response()->json(['message' => 'niet gevonden.'], 404);
});