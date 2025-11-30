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
        $startTime = microtime(true);

        $validator = Validator::make($request->all(), [
            'host' => 'required',
            'password' => 'required',
            'username' => 'required',
            'port' => 'required|integer',
        ]);

        if ($validator->fails()) {
            LogHelper::logError(
                null,
                [
                    'input' => [
                        'host' => $request->host,
                        'username' => $request->username,
                        'port' => $request->port,
                    ],
                    'validation_errors' => $validator->errors(),
                ],
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

        $router = [
            'timeout'  => 2,
            'host'     => $request->input('host'),
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'port'     => (int) $request->input('port'),
        ];

        LogHelper::logInfo(
            null,
            [
                'router_input' => [
                    'host' => $router['host'],
                    'username' => $router['username'],
                    'port' => $router['port'],
                    'has_password' => !empty($router['password']),
                ]
            ],
            200,
            "🔍 Start Mikrotik Connection Test",
            $traceId
        );

        try {
            $preConn = microtime(true);

            $response = $this->testConnectionService->testConnection($router, $traceId);

            $connDuration = round((microtime(true) - $preConn) * 1000, 2);

            LogHelper::logInfo(
                null,
                [
                    'conn_latency_ms' => $connDuration,
                    'response_raw' => $response, // tidak ada password di Mikrotik response
                ],
                200,
                "✅ Mikrotik responded successfully",
                $traceId
            );

            if (!$response) {
                throw new \Exception('Missing required system info from RouterOS', 422);
            }

            $totalDuration = round((microtime(true) - $startTime) * 1000, 2);
            $boardName = $request->input('name', $response['board_name'] ?? 'Unknown');

            LogHelper::logInfo(
                null,
                [
                    'response' => $response,
                    'total_latency_ms' => $totalDuration,
                ],
                200,
                "🎯 Successfully connected to Mikrotik: " . $router['host'],
                $traceId
            );

            return response()->json([
                'status' => 'success',
                'title' => $boardName . ' ⇌ CONNECTED',
                'message' => 'Router successfully connected!',
                'boardName' => $boardName,
                'latency_ms' => $totalDuration,
                'system_info' => [
                    'uptime' => $response['uptime'] ?? '-',
                    'cpu_load' => $response['cpu_load'] ?? '-',
                    'version' => $response['version'] ?? '-',
                ]
            ]);
        } catch (\Exception $e) {
            $totalDuration = round((microtime(true) - $startTime) * 1000, 2);

            $errorCategory = match (true) {
                str_contains($e->getMessage(), 'No route to host') => 'Network / Firewall',
                str_contains($e->getMessage(), 'invalid user name or password') => 'Authentication',
                str_contains($e->getMessage(), 'timeout') => 'Connection Timeout',
                str_contains($e->getMessage(), 'Socket') => 'Socket Failure',
                default => 'Unknown Error',
            };

            LogHelper::logError(
                null,
                [
                    'error_type' => $errorCategory,
                    'exception_message' => $e->getMessage(),
                    'latency_ms' => $totalDuration,
                    'router_host' => $router['host'],
                    'router_port' => $router['port'],
                ],
                $e->getCode() ?: 500,
                "❌ Mikrotik connection failed (" . $errorCategory . ")",
                $traceId
            );

            return response()->json([
                'status' => 'error',
                'title' => 'Router not responding!',
                'message' => $e->getMessage(),
                'error_type' => $errorCategory,
                'latency_ms' => $totalDuration
            ], $e->getCode() ?: 500);
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
