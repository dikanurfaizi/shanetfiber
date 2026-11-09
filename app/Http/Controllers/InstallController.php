<?php

namespace App\Http\Controllers;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use PDO;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;

class InstallController extends Controller
{
    public function install()
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();
        $dbName = config('database.connections.mysql.database');

        LogHelper::logInfo(
            null,
            ['database' => $dbName],
            200,
            '[INSTALLER] Membuka halaman instalasi awal.',
            $traceId,
            $timer->lap()
        );

        try {
            DB::connection()->getPdo();
            $status = 'false';
            LogHelper::logInfo(
                null,
                ['database' => $dbName],
                200,
                '[INSTALLER] Database terdeteksi sudah ada dan dapat diakses.',
                $traceId,
                $timer->lap()
            );
        } catch (\Exception $e) {
            $status = 'true';
            LogHelper::logWarning(
                null,
                ['error' => $e->getMessage()],
                400,
                '[INSTALLER] Database belum ada atau koneksi gagal.',
                $traceId,
                $timer->lap()
            );
        }

        return view('install.index', compact('status', 'dbName'));
    }

    public function start(Request $request): RedirectResponse
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        ini_set('max_execution_time', 20000);
        LogHelper::logInfo(
            null,
            [],
            200,
            '[INSTALLER] Memulai proses instalasi database...',
            $traceId,
            $timer->lap()
        );

        $this->validate(
            $request,
            ['setuju' => 'required'],
            ['setuju.required' => 'Subscribe chanel @griya-net dulu ya...']
        );

        $dbName = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        try {
            $pdo = new PDO("mysql:host={$host}", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['error' => $e->getMessage()],
                500,
                '[INSTALLER] Gagal membuat koneksi PDO awal ke MySQL.',
                $traceId,
                $timer->lap()
            );
            return redirect()->back()->withErrors(['error' => 'Gagal koneksi ke MySQL: ' . $e->getMessage()]);
        }

        try {
            DB::connection()->getPdo();
            LogHelper::logInfo(
                null,
                ['database' => $dbName],
                200,
                '[INSTALLER] Menjalankan migrate:fresh pada database yang sudah ada...',
                $traceId,
                $timer->lap()
            );

            Artisan::call('migrate:fresh', ['--force' => true]);

            LogHelper::logInfo(
                null,
                ['output' => Artisan::output()],
                200,
                '[INSTALLER] migrate:fresh berhasil dieksekusi.',
                $traceId,
                $timer->lap()
            );
        } catch (\Exception $e) {
            LogHelper::logInfo(
                null,
                ['db_name' => $dbName],
                200,
                '[INSTALLER] Menjalankan query pembuatan database baru...',
                $traceId,
                $timer->lap()
            );

            try {
                $pdo->query('CREATE DATABASE IF NOT EXISTS `' . $dbName . '`');
                LogHelper::logInfo(
                    null,
                    ['db_name' => $dbName],
                    200,
                    '[INSTALLER] Database baru berhasil dibuat.',
                    $traceId,
                    $timer->lap()
                );

                // Refresh koneksi DB Laravel agar mengenali database yang baru dibuat
                DB::purge('mysql');
                DB::reconnect('mysql');

                LogHelper::logInfo(
                    null,
                    [],
                    200,
                    '[INSTALLER] Menjalankan artisan migrate & key:generate...',
                    $traceId,
                    $timer->lap()
                );

                Artisan::call('migrate', ['--force' => true]);
                LogHelper::logInfo(
                    null,
                    ['output' => Artisan::output()],
                    200,
                    '[INSTALLER] Artisan migrate berhasil.',
                    $traceId,
                    $timer->lap()
                );

                Artisan::call('key:generate', ['--force' => true]);
                LogHelper::logInfo(
                    null,
                    ['output' => Artisan::output()],
                    200,
                    '[INSTALLER] Artisan key:generate berhasil.',
                    $traceId,
                    $timer->lap()
                );
            } catch (\Exception $ex) {
                LogHelper::logError(
                    null,
                    ['error' => $ex->getMessage()],
                    500,
                    '[INSTALLER] Gagal saat proses migrasi database baru.',
                    $traceId,
                    $timer->lap()
                );
                throw $ex;
            }
        }

        LogHelper::logInfo(
            null,
            [],
            200,
            '[INSTALLER] Tahap migrasi selesai, lanjut ke halaman seeder.',
            $traceId,
            $timer->lap()
        );

        return redirect()->route('installSeeder');
    }

    public function seeder(): View
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();
        $dbName = config('database.connections.mysql.database');

        try {
            DB::connection()->getPdo();
            $status = 'true';
            LogHelper::logInfo(
                null,
                ['database' => $dbName],
                200,
                '[INSTALLER] Halaman seeder diakses. Status DB OK.',
                $traceId,
                $timer->lap()
            );
        } catch (\Exception $e) {
            $status = 'false';
            LogHelper::logError(
                null,
                ['error' => $e->getMessage()],
                500,
                '[INSTALLER] Halaman seeder diakses tetapi koneksi DB bermasalah.',
                $traceId,
                $timer->lap()
            );
        }

        return view('install.seeder', compact('status', 'dbName'));
    }

    public function create_seeder(): RedirectResponse
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        LogHelper::logInfo(
            null,
            [],
            200,
            '[INSTALLER] Memulai proses eksekusi seeder...',
            $traceId,
            $timer->lap()
        );

        try {
            Artisan::call('db:seed', ['--class' => 'PermissionTableSeeder', '--force' => true]);
            LogHelper::logInfo(null, [], 200, '[INSTALLER] PermissionTableSeeder berhasil.', $traceId, $timer->lap());

            Artisan::call('db:seed', ['--class' => 'CreatePppTypeSeeder', '--force' => true]);
            LogHelper::logInfo(null, [], 200, '[INSTALLER] CreatePppTypeSeeder berhasil.', $traceId, $timer->lap());

            Artisan::call('db:seed', ['--class' => 'WhatsappGatewayTableSeeder', '--force' => true]);
            LogHelper::logInfo(null, [], 200, '[INSTALLER] WhatsappGatewayTableSeeder berhasil.', $traceId, $timer->lap());

            Artisan::call('db:seed', ['--class' => 'WhatsappMessageTableSeeder', '--force' => true]);
            LogHelper::logInfo(null, [], 200, '[INSTALLER] WhatsappMessageTableSeeder berhasil.', $traceId, $timer->lap());
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['error' => $e->getMessage()],
                500,
                '[INSTALLER] Terjadi kesalahan saat menjalankan seeder.',
                $traceId,
                $timer->lap()
            );
            throw $e;
        }

        LogHelper::logInfo(
            null,
            [],
            200,
            '[INSTALLER] Seluruh seeder selesai dieksekusi. Lanjut ke pembuatan admin.',
            $traceId,
            $timer->lap()
        );

        return redirect()->route('installCreateAdmin');
    }

    public function create_admin(): View
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        LogHelper::logInfo(
            null,
            [],
            200,
            '[INSTALLER] Membuka form registrasi akun admin.',
            $traceId,
            $timer->lap()
        );

        return view('install.create_admin');
    }

    public function store_admin(Request $request): RedirectResponse
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        LogHelper::logInfo(
            null,
            ['email' => $request->input('email')],
            200,
            '[INSTALLER] Memproses pembuatan akun admin...',
            $traceId,
            $timer->lap()
        );

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:password_confirmation',
        ]);

        try {
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            LogHelper::logInfo(
                null,
                ['user_id' => $user->id, 'email' => $user->email],
                200,
                '[INSTALLER] User admin berhasil dibuat.',
                $traceId,
                $timer->lap()
            );

            $role = Role::create(['name' => 'Admin']);
            $permissions = Permission::pluck('id', 'id')->all();
            $role->syncPermissions($permissions);
            $user->assignRole([$role->id]);

            LogHelper::logInfo(
                null,
                ['role_id' => $role->id],
                200,
                '[INSTALLER] Role Admin dan Permission berhasil di-assign ke user admin.',
                $traceId,
                $timer->lap()
            );
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['error' => $e->getMessage()],
                500,
                '[INSTALLER] Gagal membuat akun admin atau meassign role.',
                $traceId,
                $timer->lap()
            );
            throw $e;
        }

        return redirect()->route('installFinish');
    }

    public function finish(): View
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        LogHelper::logInfo(
            null,
            [],
            200,
            '[INSTALLER] Instalasi seluruh sistem telah SELESAI diselesaikan.',
            $traceId,
            $timer->lap()
        );

        return view('install.finish_install');
    }
}
