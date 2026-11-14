<?php

namespace App\Http\Controllers;

use PDO;
use App\Models\User;
use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use App\Http\Requests\StoreAdminRequest;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class InstallController extends Controller
{
    public function install()
    {
        $dbName = config('database.connections.mysql.database');
        try {
            DB::connection()->getPdo();
            $status = 'false';
        } catch (\Exception $e) {
            $status = 'true';
        }

        return view('install.index', compact('status', 'dbName'));
    }

    public function start(Request $request): RedirectResponse
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();
        ini_set('max_execution_time', 20000);

        $this->validate(
            $request,
            ['setuju' => 'required'],
            ['setuju.required' => 'Mohon setujui ketentuan instalasi terlebih dahulu...']
        );

        // Ambil kredensial Root/Admin untuk setup awal
        $host = env('DB_INSTALL_HOST', 'localhost');
        $port = env('DB_INSTALL_PORT', '3306');
        $rootUser = env('DB_INSTALL_USERNAME', 'root');
        $rootPass = env('DB_INSTALL_PASSWORD', '');

        // Ambil detail database dan user khusus aplikasi yang ingin dibuat
        $targetDb = config('database.connections.mysql.database', 'shanetmgmt');
        $appUser = config('database.connections.mysql.username', 'shanet_app_user');
        $appPass = config('database.connections.mysql.password', '');

        try {
            // 1. Koneksi ke MySQL sebagai ROOT / ADMIN
            $pdo = new PDO("mysql:host={$host};port={$port}", $rootUser, $rootPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // 2. Buat Database jika belum ada
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$targetDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // 3. Buat User Khusus Schema/Database tersebut (mendukung dengan atau tanpa password)
            if (!empty($appPass)) {
                $pdo->exec("CREATE USER IF NOT EXISTS '{$appUser}'@'localhost' IDENTIFIED BY '{$appPass}'");
                $pdo->exec("CREATE USER IF NOT EXISTS '{$appUser}'@'%' IDENTIFIED BY '{$appPass}'");
            } else {
                $pdo->exec("CREATE USER IF NOT EXISTS '{$appUser}'@'localhost'");
                $pdo->exec("CREATE USER IF NOT EXISTS '{$appUser}'@'%'");
            }

            // 4. Berikan Hak Akses (Privileges) penuh hanya ke database tersebut
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$targetDb}`.* TO '{$appUser}'@'localhost'");
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$targetDb}`.* TO '{$appUser}'@'%'");
            $pdo->exec("FLUSH PRIVILEGES");

            LogHelper::logInfo(null, ['target_db' => $targetDb, 'app_user' => $appUser], 200, "Database successfully created/configured.", $traceId, $timer->lap());
        } catch (\PDOException $e) {
            LogHelper::logInfo(null, ['error' => $e->getMessage()], 500, "Database Setup Failed.", $traceId, $timer->lap());
            return back()->withErrors(['setuju' => 'Setup Database Gagal: ' . $e->getMessage()]);
        }

        // 5. Jalankan Migrasi menggunakan User Aplikasi yang baru dibuat
        try {
            DB::reconnect('mysql');
            Artisan::call('migrate:fresh', ['--force' => true]);
            Artisan::call('key:generate', ['--force' => true]);

            LogHelper::logInfo(null, [], 200, "Database migration and key generation completed successfully.", $traceId, $timer->lap());
        } catch (\Exception $e) {
            LogHelper::logInfo(null, ['error' => $e->getMessage()], 500, "Migration Failed.", $traceId, $timer->lap());
            return back()->withErrors(['setuju' => 'Migrasi Gagal: ' . $e->getMessage()]);
        }

        return redirect()->route('installSeeder');
    }

    public function seeder(): View
    {
        $dbName = config('database.connections.mysql.database');
        try {
            DB::connection()->getPdo();
            $status = 'true';
        } catch (\Exception $e) {
            $status = 'false';
        }
        return view('install.seeder', compact('status', 'dbName'));
    }

    public function create_seeder(): RedirectResponse
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();

        try {
            Artisan::call('db:seed --class=PermissionTableSeeder');
            Artisan::call('db:seed --class=CreatePppTypeSeeder');
            // Artisan::call('db:seed --class=WebsystemSeeder');
            Artisan::call('db:seed --class=WhatsappGatewayTableSeeder');
            Artisan::call('db:seed --class=WhatsappMessageTableSeeder');

            LogHelper::logInfo(null, [], 200, "All installation seeders executed successfully.", $traceId, $timer->lap());
        } catch (\Exception $e) {
            LogHelper::logInfo(null, ['error' => $e->getMessage()], 500, "Seeder Execution Failed.", $traceId, $timer->lap());
        }

        return redirect()->route('installCreateAdmin');
    }

    public function create_admin(): View
    {
        return view('install.create_admin');
    }

    public function store_admin(StoreAdminRequest $request): RedirectResponse
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();

        try {
            $input = $request->validated();
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);

            $role = Role::create(['name' => 'Admin']);
            $permissions = Permission::pluck('id', 'id')->all();
            $role->syncPermissions($permissions);
            $user->assignRole([$role->id]);

            LogHelper::logInfo(null, ['email' => $user->email], 200, "Initial admin user created successfully during installation.", $traceId, $timer->lap());
        } catch (\Exception $e) {
            LogHelper::logInfo(null, ['error' => $e->getMessage()], 500, "Failed to create admin user during installation.", $traceId, $timer->lap());
            return back()->withErrors(['email' => 'Gagal membuat akun admin: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('installFinish');
    }

    public function finish(): View
    {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();

        LogHelper::logInfo(null, [], 200, "Installation wizard completed successfully.", $traceId, $timer->lap());

        return view('install.finish_install');
    }
}
