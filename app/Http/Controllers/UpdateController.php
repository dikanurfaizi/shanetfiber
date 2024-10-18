<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Migration;
use App\Models\Websystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\PermissionRegistrar;


class UpdateController extends Controller
{
    protected $newVersion;
    protected $version;
    public function __construct()
    {
        $this->middleware('permission:server-edit', ['only' => ['index', 'update']]);
        $this->newVersion = '1.0.8b';
        $this->version = Websystem::where('id', 1)->value('version');
        if ($this->version == null) {
            $this->version = Websystem::where('slug', 'websystem')->value('version');
        }
    }
    public function index()
    {
        $version = $this->version;
        $newVersion = $this->newVersion;
        return view('updates.index', compact('newVersion', 'version'));
    }

    public function updated()
    {
        if ($this->version != $this->newVersion) {

            //Artisan::call('migrate');
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed --class=AdminPermissionTableSeeder');
            Websystem::where('id', 1)->update([
                'version' => $this->newVersion,
            ]);


            return redirect()->back()
                ->with('success', 'Version updated to ' . $this->newVersion . ' successfully.');
        } else {
            return redirect()->back()
                ->with('error', 'Customer management is latest version!');
        }
    }

    public function optimize()
    {
        if ($this->version == $this->newVersion) {
            Artisan::call('optimize:clear');
            return redirect()->back()
                ->with('success', 'Optimize successfully.');
        } else {
            return redirect()->back()
                ->with('error', 'Failed optimizing!');
        }
    }
}
