<?php

use App\Http\Controllers\Billing\BillingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentGateway\TripayCallbackController;
use App\Http\Controllers\Mikrotik\MikrotikMessageController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\WhatsApp\WhatsAppWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('tripay/callback', [TripayCallbackController::class, 'handle']);
Route::middleware('mikrotik.auth')->post('/mikrotik/send-message', [MikrotikMessageController::class, 'send']);
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle']);
Route::get('/public/receipts/download/{token}', [ReceiptController::class, 'printPreview'])->name('public.receipts.download');
Route::get('/public/paylater/info/{token}', [BillingController::class, 'paylaterPreview'])->name('public.paylater.download');
