<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Http\Requests\BankUpdateRequest;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BankController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:is-admin', ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy', 'enable', 'disable']]);
    }

    public function index()
    {
        $banks = Bank::paginate(10);
        return view('banks.index', compact('banks'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function create()
    {
        $path = public_path('data/indonesia-bank.json');
        $banks = [];

        if (file_exists($path)) {
            $banks = json_decode(file_get_contents($path), true);
        }

        return view('banks.create', compact('banks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name'           => 'required',
                'bank'           => 'required',
                'account_number' => 'required',
                'type'           => 'required|in:bank,ewallet', // 👈 Validasi tipe dikunci di sini
            ],
            [
                'name.required'           => __('bank.validation.name_required'),
                'bank.required'           => __('bank.validation.bank_required'),
                'account_number.required' => __('bank.validation.account_number_required'),
                'type.required'           => __('bank.validation.type_required'),
                'type.in'                 => __('bank.validation.type_in'),
            ]
        );

        // Otomatis men-set default disabled ke 'false' (aktif) saat pertama kali disimpan 
        // agar sesuai dengan spesifikasi enum ('false', 'true') di skema database Anda
        $request->request->add([
            'slug'      => str($request->bank . '-' . \Illuminate\Support\Str::random(4))->slug(),
            'is_active' => false
        ]);

        Bank::create($request->all());

        return redirect()->route('banks.index')
            ->with('success', __('bank.notification.store_success'));
    }

    public function show(Bank $bank)
    {
        $connectedAdmins = $bank->users()
            ->orderBy('users.name', 'asc')
            ->paginate(3, ['*'], 'admins_page');

        $connectedUserIds = $bank->users()->pluck('users.id')->toArray();

        $availableUsers = User::whereNotIn('id', $connectedUserIds)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id');

        return view('banks.show', compact('bank', 'connectedAdmins', 'availableUsers'));
    }

    public function edit(Bank $bank)
    {
        $path = public_path('data/indonesia-bank.json');
        $banks = [];

        if (file_exists($path)) {
            $banks = json_decode(file_get_contents($path), true);
        }

        // Pilihan $users dihilangkan dari compact sesuai kebutuhan pembersihan form edit Anda
        return view('banks.edit', compact('bank', 'banks'));
    }

    public function update(BankUpdateRequest $request, Bank $bank)
    {
        // Catatan: Pastikan di dalam file 'BankUpdateRequest' Anda juga sudah menghapus rule validasi 'user_id'
        $bank->update($request->all());

        return redirect()->route('banks.index')
            ->with('success', __('bank.notification.update_success'));
    }

    public function enable(Bank $bank)
    {
        $bank->update([
            'is_active' => true,
        ]);

        // 🔥 Menggunakan translasi parameter dinamis lewat array :attribute pengganti concatenation string
        return redirect()->back()->with('success', __('bank.notification.enable_success', [
            'name' => $bank->name,
            'bank' => $bank->bank,
            'account_number' => $bank->account_number
        ]));
    }

    public function disable(Bank $bank)
    {
        $bank->update([
            'is_active' => false,
        ]);

        // 🔥 Menggunakan translasi parameter dinamis lewat array :attribute pengganti concatenation string
        return redirect()->back()->with('success', __('bank.notification.disable_success', [
            'name' => $bank->name,
            'bank' => $bank->bank,
            'account_number' => $bank->account_number
        ]));
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();

        return redirect()->route('banks.index')
            ->with('success', __('bank.notification.destroy_success', [
                'name' => $bank->name,
                'bank' => $bank->bank,
                'account_number' => $bank->account_number
            ]));
    }

    /**
     * Menghubungkan Admin Wilayah ke Rekening Terpusat
     */
    public function linkUser(Request $request, Bank $bank)
    {
        LogHelper::logDebug(null, [
            "bank_id" => $bank->id,
            "user_id" => $request->user_id
        ], 200, "BankController@linkUser initiated");

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $bank->users()->syncWithoutDetaching([$request->user_id]);

            LogHelper::logInfo(null, [
                "bank_id" => $bank->id,
                "user_id" => $request->user_id
            ], 200, "BankController@linkUser successfully executed");

            return redirect()->route('banks.show', $bank)
                ->with('success', __('bank.notification.link_success'));
        } catch (\Exception $e) {
            LogHelper::logError($e, [
                "bank_id" => $bank->id,
                "user_id" => $request->user_id
            ], 500, "BankController@linkUser failed");

            return redirect()->back()
                ->withInput()
                ->with('error', __('bank.notification.link_failed'));
        }
    }

    /**
     * Memutus Akses Admin Wilayah dari Rekening Terpusat
     */
    public function unlinkUser(Request $request, Bank $bank)
    {
        LogHelper::logDebug(null, [
            "bank_id" => $bank->id,
            "user_id" => $request->user_id
        ], 200, "BankController@unlinkUser initiated");

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $bank->users()->detach($request->user_id);

            LogHelper::logInfo(null, [
                "bank_id" => $bank->id,
                "user_id" => $request->user_id
            ], 200, "BankController@unlinkUser successfully executed");

            return redirect()->route('banks.show', $bank)
                ->with('success', __('bank.notification.unlink_success'));
        } catch (\Exception $e) {
            LogHelper::logError($e, [
                "bank_id" => $bank->id,
                "user_id" => $request->user_id
            ], 500, "BankController@unlinkUser failed");

            return redirect()->back()
                ->with('error', __('bank.notification.unlink_failed'));
        }
    }
}
