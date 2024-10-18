<?php

namespace App\Http\Controllers\WhatsappGateway;

use App\Models\Websystem;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\WhatsAppGateway;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse; // Pastikan ini benar

class WhatsappGatewayController extends Controller
{
    public function __construct()
    {
        // Mengatur middleware untuk memastikan hanya admin yang bisa mengakses fungsi ini
        $this->middleware('permission:is-admin', ['only' => ['index', 'edit', 'update', 'store']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mengambil parameter pencarian dari request
        $search = request()->get('search');

        // Mencari WhatsappGateways berdasarkan nama atau deskripsi
        $whatsappGateways = WhatsAppGateway::when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        })->paginate(10);

        // Mengambil Websystem
        $webSystem = Websystem::where('slug', 'websystem')->first();

        // Mengembalikan view dengan data yang diperlukan
        return view('whatsapp_gateways.index', compact('whatsappGateways', 'webSystem'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Menampilkan form untuk membuat WhatsApp Gateway baru
        return view('whatsapp_gateways.send_message.create'); // Sesuaikan dengan lokasi view
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Konversi nilai dropdown dari string ke integer
        $request->merge([
            'schedule_hour' => (int) $request->input('schedule_hour'),
            'schedule_minute' => (int) $request->input('schedule_minute'),
            'schedule_second' => (int) $request->input('schedule_second')
        ]);

        // Validasi input dengan regex untuk menghindari karakter berbahaya
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|regex:/^[a-zA-Z0-9\s]*$/', // Validasi hanya alfanumerik dan spasi
            'description' => 'required|string',
            'status' => 'nullable|in:active,inactive',
            'schedule_date' => 'required|integer|min:1|max:31',
            'schedule_hour' => 'required|integer|min:0|max:23',
            'schedule_minute' => 'required|integer|min:0|max:59',
            'schedule_second' => 'required|integer|min:0|max:59',
            'message' => 'required|string',
        ]);

        // Gabungkan jam, menit, detik ke dalam format HH:mm:ss
        $schedule_time = sprintf('%02d:%02d:%02d', $request->input('schedule_hour'), $request->input('schedule_minute'), $request->input('schedule_second'));

        // Sanitasi input dengan mengonversi karakter spesial
        $sanitizedInput = [
            'name' => htmlspecialchars($request->input('name'), ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars($request->input('description'), ENT_QUOTES, 'UTF-8'),
            'status' => $request->input('status', 'inactive'), // Default ke 'inactive' jika tidak ada status
            'schedule_date' => $request->input('schedule_date'),
            'schedule_time' => $schedule_time,
            'message' => htmlspecialchars($request->input('message'), ENT_QUOTES, 'UTF-8'),
        ];

        // Simpan data ke database
        WhatsAppGateway::create($sanitizedInput);

        // Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('whatsapp-gateway.index')->with('success', 'WhatsApp Broadcast created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $whatsappGateway = WhatsAppGateway::findOrFail($id);

        $time = $whatsappGateway->schedule_time;
        $hour = Carbon::createFromFormat('H:i:s', $time)->format('H');
        $minute = Carbon::createFromFormat('H:i:s', $time)->format('i');
        $second = Carbon::createFromFormat('H:i:s', $time)->format('s');

        return view('whatsapp_gateways.send_message.edit', compact('whatsappGateway', 'hour', 'minute', 'second'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Konversi nilai dropdown dari string ke integer
        $request->merge([
            'schedule_hour' => (int) $request->input('schedule_hour'),
            'schedule_minute' => (int) $request->input('schedule_minute'),
            'schedule_second' => (int) $request->input('schedule_second')
        ]);

        // Lakukan validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|regex:/^[a-zA-Z0-9\s]*$/', // Hanya menerima alfanumerik dan spasi
            'status' => 'required|string|in:active,inactive',
            'description' => 'required|string',
            'schedule_hour' => 'required|integer|min:0|max:23|not_in:-1',
            'schedule_minute' => 'required|integer|min:0|max:59|not_in:-1', // Menolak nilai -1
            'schedule_second' => 'required|integer|min:0|max:59|not_in:-1', // Menolak nilai -1
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Jika validasi gagal, kembali ke halaman sebelumnya dengan pesan error
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Gabungkan jam, menit, dan detik ke dalam format HH:mm:ss
        $schedule_time = sprintf('%02d:%02d:%02d', $request->input('schedule_hour'), $request->input('schedule_minute'), $request->input('schedule_second'));

        // Cari data berdasarkan ID
        $whatsappGateway = WhatsAppGateway::findOrFail($id);

        // Sanitasi input dengan mengonversi karakter spesial menggunakan htmlspecialchars
        $sanitizedInput = [
            'name' => htmlspecialchars($request->input('name'), ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars($request->input('description'), ENT_QUOTES, 'UTF-8'),
            'status' => $request->input('status', 'inactive'), // Default ke 'inactive' jika tidak ada status
            'schedule_date' => $request->input('schedule_date'),
            'schedule_time' => $schedule_time,
            'message' => htmlspecialchars($request->input('message'), ENT_QUOTES, 'UTF-8'),
        ];

        // Update data
        $whatsappGateway->update($sanitizedInput);

        // Redirect setelah berhasil mengupdate data
        return redirect()->route('whatsapp-gateway.index')->with('success', 'WhatsApp Broadcast updated successfully!');
    }


    /**
     * Display the specified resource.
     */
    public function show(WhatsappGateway $whatsappGateway)
    {
        return view('whatsapp_gateways.show', compact('whatsappGateway'));
    }

    public function destroy($id)
    {
        // Cari WhatsApp Gateway berdasarkan ID
        $whatsappGateway = WhatsAppGateway::find($id);

        // Cek apakah data ditemukan
        if (!$whatsappGateway) {
            return redirect()->route('whatsapp-gateway.index')->with('error', 'WhatsApp Broadcast not found.');
        }

        // Hapus data
        $whatsappGateway->delete();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('whatsapp-gateway.index')->with('success', 'WhatsApp Broadcast deleted successfully.');
    }

    public function getCustomers(): JsonResponse
    {
        // Ambil semua customer
        $customers = Customer::all(['contact']); // Ambil hanya kolom contact

        return response()->json($customers);
    }
}
