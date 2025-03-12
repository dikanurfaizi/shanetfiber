<?php

namespace App\Http\Controllers\PaymentGateway;

use App\Models\Tripay;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Traits\TripayHelper;
use App\Helpers\LogHelper;

class TripayController extends Controller
{
    use TripayHelper;

    public function someFunction()
    {
        $tripay = $this->getTripayData();
        if (!$tripay) {
            return response()->json(['status' => 'error', 'message' => 'Tripay config not found'], 404);
        }

        return $tripay;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tripay $tripay)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tripay $tripay)
    {
        return view('payment_gateways.tripays.edit', compact('tripay'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tripay $tripay)
    {
        if ($request->disabled) {
            // $disabled = 'true';
            $request->request->add(['disabled' => 'true']);
            $request->request->add(['tripays_is_production' => 'false']);
        } else {
            // $disabled = 'false';
            $request->request->add(['disabled' => 'false']);
            if ($request->tripays_is_production == NULL) {
                $request->request->add(['tripays_is_production' => 'false']);
                // $tripays_is_production = 'false';
                $this->validate(
                    $request,
                    [
                        'tripays_merchant_id' => 'required',
                        'tripays_sandbox_api_key' => 'required',
                        'tripays_sandbox_private_key' => 'required',
                    ],
                    [
                        'tripays_merchant_id.required' => 'Merchant id required if enable Tripays payment gateway.',
                        'tripays_sandbox_api_key.required' => 'Sandbox client key required to try payment gateway.',
                        'tripays_sandbox_private_key.required' => 'Sandbox server key required to try payment gateway.'

                    ]
                );
            } else {

                //$tripays_is_production = 'true';
                $request->request->add(['tripays_is_production' => 'true']);
                $this->validate(
                    $request,
                    [
                        'tripays_merchant_id' => 'required',
                        'tripays_api_key' => 'required',
                        'tripays_private_key' => 'required',
                    ],
                    [
                        'tripays_merchant_id.required' => 'Merchant id required if enable Tripays payment gateway.',
                        'tripays_api_key.required' => 'Production client key required for production mode.',
                        'tripays_private_key.required' => 'Production server key required for production mode.'

                    ]
                );
            }
        }

        $input = $request->all();
        $tripay->update($input);
        return redirect()->back()->with('success', 'Configuration tripays successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tripay $tripay)
    {
        //
    }

    public function manipulateContact($contact, $action = null)
    {
        try {
            // Hapus karakter non-digit, kecuali tanda "+" di awal
            $contact = preg_replace('/[^\d+]/', '', $contact);

            // Jika nomor diawali dengan "+62", ubah menjadi "62"
            if (str_starts_with($contact, '+62')) {
                $contact = substr($contact, 1);
            }

            if ($action === 'broadcast') {
                // Jika nomor dimulai dengan "08", ubah ke format "62"
                if (str_starts_with($contact, '08')) {
                    $contact = '62' . substr($contact, 1);
                }
            } else {
                // Jika nomor diawali dengan "628", langsung ubah menjadi "08"
                $contact = preg_replace('/^628/', '08', $contact);
            }

            return $contact;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                null,
                500,
                "Error manipulating contact bill info for contact: $contact | Action: $action | " . $e->getMessage()
            );
            return null;
        }
    }

    public function getPaymentChannels()
    {
        $tripay = $this->getTripayData();

        // Tentukan URL & API Key berdasarkan mode (Production / Sandbox)
        $url = $tripay->tripays_is_production === 'false'
            ? config('tripay.sandbox_url') . '/merchant/payment-channel'
            : config('tripay.production_url') . '/merchant/payment-channel';

        $apiKey = $tripay->tripays_is_production === 'false'
            ? $tripay->tripays_sandbox_api_key
            : $tripay->tripays_api_key;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL verification
            CURLOPT_SSL_VERIFYHOST => false,  // Disable SSL host verification
            CURLOPT_TIMEOUT        => 5,  // Timeout total request (5 detik)
            CURLOPT_CONNECTTIMEOUT => 3,  // Timeout saat mencoba koneksi (3 detik)
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        // Cek apakah respons cURL valid
        if ($error) {
            return response()->json(['error' => $error], 500);
        }

        // Decode respons JSON
        $decodedResponse = json_decode($response, true);

        // Pastikan respons memiliki key "data"
        if (!isset($decodedResponse['data'])) {
            return response()->json(['error' => 'Invalid response from Tripay'], 500);
        }

        return $decodedResponse['data'] ?? [];
    }

    public function getSignature(Order $order)
    {
        $tripay = $this->getTripayData();

        // Tentukan URL & API Key berdasarkan mode (Production / Sandbox)
        $tripay_private_key = $tripay->tripays_is_production === 'false'
            ? $tripay->tripays_sandbox_private_key
            : $tripay->tripays_private_key;

        $signature = hash_hmac('sha256', $tripay->tripays_merchant_id . $order->number . $order->total_price, $tripay_private_key);

        return $signature;
    }

    public function requestTransaction(Order $order)
    {
        $tripay = $this->getTripayData();

        $tripayUri = $tripay->tripays_is_production === 'false'
            ? config('tripay.sandbox_url') . '/transaction/create'
            : config('tripay.production_url') . '/transaction/create';

        $apiKey = $tripay->tripays_is_production === 'false'
            ? $tripay->tripays_sandbox_api_key
            : $tripay->tripays_api_key;

        $data = [
            'method'         => $order->payment_channel_code,
            'merchant_ref'   => $order->number,
            'amount'         => $order->total_price,
            'customer_name'  => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $this->manipulateContact($order->customer_phone) ?? null,
            'order_items'    => [
                [
                    'name'     => $order->package,
                    'price'    => $order->total_price,
                    'quantity' => 1,
                ]
            ],
            'expired_time' => time() + (24 * 60 * 60), // 24 jam
            'signature'    => $order->signature,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $tripayUri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return response()->json(['error' => 'cURL Error: ' . $error], 500);
        }

        $decodedResponse = json_decode($response, true);

        if (!$decodedResponse) {
            return response()->json(['error' => 'Invalid response format from Tripay'], 500);
        }

        if (!isset($decodedResponse['data'])) {
            return response()->json([
                'error'   => 'Invalid response from Tripay',
                'message' => $decodedResponse['message'] ?? 'Unknown error',
            ], 500);
        }

        return $decodedResponse['data'];
    }
}
