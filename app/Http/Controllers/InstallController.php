<?php

namespace App\Http\Controllers;



use PDO;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        ini_set('max_execution_time', 20000);
        $this->validate(
            $request,
            [
                'setuju' => 'required',
            ],

            [
                'setuju.required' => 'Subscribe chanel @griya-net dulu ya...',
            ]
        );

        $dbName = config('database.connections.mysql.database');
        $pdo = new PDO(
            'mysql:host=' . config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password')
        );

        try {
            DB::connection()->getPdo();
            Artisan::call('migrate:fresh');
        } catch (\Exception $e) {
            $pdo->query('CREATE DATABASE ' . $dbName);
            Artisan::call('migrate');
            Artisan::call('key:generate');
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

        Artisan::call('db:seed --class=PermissionTableSeeder');
        Artisan::call('db:seed --class=CreatePppTypeSeeder');
        // Artisan::call('db:seed --class=WebsystemSeeder');
        Artisan::call('db:seed --class=WhatsappGatewayTableSeeder');
        Artisan::call('db:seed --class=WhatsappMessageTableSeeder');
        return redirect()->route('installCreateAdmin');
    }
    public function create_admin(): View
    {
        return view('install.create_admin');
    }

    public function store_admin(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:password_confirmation',
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        //$user->assignRole($request->input('roles'));

        $role = Role::create(['name' => 'Admin']);
        $permissions = Permission::pluck('id', 'id')->all();
        $role->syncPermissions($permissions);
        $user->assignRole([$role->id]);
        //Artisan::call('db:seed --class=CreateAdminUserSeeder');
        return redirect()->route('installFinish');
    }

    public function finish(): View
    {
        return view('install.finish_install');
    }
}
