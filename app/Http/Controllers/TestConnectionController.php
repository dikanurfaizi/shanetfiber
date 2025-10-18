<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Mikrotik;
use App\Services\Routerboard\TestConnectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TestConnectionController extends Controller
{
    private TestConnectionService $testConnectionService;

    public function __construct(TestConnectionService $testConnectionService)
    {
        $this->testConnectionService = $testConnectionService;
    }

    public function testConn(Request $request)
    {
        $traceId = (string) Str::uuid();

        $validator = Validator::make($request->all(), [
            'host' => 'required',
            'password' => 'required',
            'username' => 'required',
            'port' => 'required',
        ], [
            'host.required' => 'Host harus diisi.',
            'username.required' => 'Username harus diisi.',
            'password.required' => 'Password harus diisi.',
            'port.required' => 'Port harus diisi.',
        ]);

        if ($validator->fails()) {
            LogHelper::logError(
                null,
                ['validation_errors' => $validator->errors()],
                400,
                'Validation failed for Mikrotik connection test',
                $traceId
            );

            return response()->json([
                'status' => 'error',
                'title' => 'Gagal terkoneksi!',
                'errors' => $validator->errors(),
            ], 400);
        }

        // 🔒 ubah array menjadi object agar seragam dengan Mikrotik model
        $router = (object)[
            'timeout'  => 1,
            'host'     => $request->input('host'),
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'port'     => (int) $request->input('port'),
        ];

        try {
            $response = $this->testConnectionService->testConnection($router, $traceId);

            $boardName = $response['data']['board-name'] ?? 'Unknown';

            if ($boardName === 'Unknown') {
                LogHelper::logError(null, [
                    'error' => [
                        'code' => 422,
                        'message' => 'Router did not respond with valid board name.'
                    ]
                ], 422, "Router did not respond with valid board name: " . $router->name, $traceId);
                return response()->json([
                    'status' => 'error',
                    'title' => 'Router sedang offline!',
                    'message' => 'Router tidak merespon / tidak bisa diakses.',
                ], 500);
            }

            LogHelper::logInfo(
                null,
                ['response' => $response],
                200,
                "Successfully connected to Mikrotik: " . $router->host,
                $traceId
            );

            return response()->json([
                'status' => 'success',
                'title' => $boardName . ' ⇌ CONNECTED',
                'message' => 'Silahkan lanjutkan proses penambahan router!',
                'boardName' => $boardName,
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $message = $e->getMessage();

            if ($code === 422 && str_contains($message, 'board name')) {
                $message = 'Router tidak merespon dengan informasi sistem. Periksa kembali koneksi atau kredensial login Mikrotik.';
            }

            LogHelper::logError(null, [
                'error' => [
                    'code' => $code,
                    'message' => $e->getMessage(),
                ]
            ], $code ?: 500, "Router did not respond with valid board name: " . $router->name, $traceId);

            return response()->json([
                'status' => 'error',
                'title' => 'Router tidak merespon!',
                'message' => $message,
            ], $code ?: 500);
        }
    }

    public function checkOnline(Request $request)
    {
        $traceId = (string) Str::uuid();
        $router = Mikrotik::findOrFail($request->id);
        try {
            $response = $this->testConnectionService->checkOnline($router, $traceId);

            LogHelper::logInfo(null, ['response' => $response], 200, "Router Online: " . $router->name, $traceId);

            $boardName = $response['data']['board-name'] ?? 'Unknown';

            if ($boardName === 'Unknown') {
                LogHelper::logError(null, [
                    'error' => [
                        'code' => 422,
                        'message' => 'Router did not respond with valid board name.'
                    ]
                ], 422, "Router did not respond with valid board name: " . $router->name, $traceId);
                return response()->json([
                    'status' => 'error',
                    'title' => 'Router sedang offline!',
                    'message' => 'Router tidak merespon / tidak bisa diakses.',
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'title' => $router->name . ' ⇌ CONNECTED',
                'message' => 'Router ⇌ ' . $boardName . ' ⇌ Online',
                'boardName' => $boardName
            ]);
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'error' =>
                [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500, "Router Offline: " . $router->name, $traceId);
            return response()->json([
                'status' => 'error',
                'title' => 'Router sedang offline!',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
