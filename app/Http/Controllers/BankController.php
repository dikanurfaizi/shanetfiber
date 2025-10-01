<?php

namespace App\Http\Controllers;

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
        $users = User::all();

        // Baca file json dari public
        $path = public_path('data/indonesia-bank.json');
        $banks = [];

        if (file_exists($path)) {
            $banks = json_decode(file_get_contents($path), true);
        }

        return view('banks.create', compact('users', 'banks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => 'required',
                'bank' => 'required',
                'account_number' => 'required',
                'user_id' => 'required'
            ],
            [
                'name.required' => 'Account Name required!',
                'bank.required' => 'Bank Name required!',
                'account_number.required' => 'Account Number required!',
                'user_id.required' => 'Admin Area required!'
            ]
        );

        $request->request->add(['slug' => str($request->bank . '-' . Str::random(4))->slug()]);
        Bank::create($request->all());

        return redirect()->route('banks.index')
            ->with('success', 'Account bank created successfully.');
    }
    public function show(Bank $bank)
    {
        //return view('packets.show', compact('packet'));
    }
    public function edit(Bank $bank)
    {
        $users = User::All();
        // Baca file json dari public
        $path = public_path('data/indonesia-bank.json');
        $banks = [];

        if (file_exists($path)) {
            $banks = json_decode(file_get_contents($path), true);
        }
        return view('banks.edit', compact('bank', 'users', 'banks'));
    }
    public function update(BankUpdateRequest $request, Bank $bank)
    {
        $bank->update($request->all());
        return redirect()->route('banks.index')
            ->with('success', 'Account bank updated successfully');
    }
    public function enable(Bank $bank)
    {
        $bank->update([
            'disabled' => 'false',
        ]);
        return redirect()->back()->with('success', 'Account ' . $bank->name . ' Bank ' . $bank->bank . ' Account Number ' . $bank->account_number . ' enable');
    }

    public function disable(Bank $bank)
    {
        $bank->update([
            'disabled' => 'true',
        ]);
        return redirect()->back()->with('success', 'Account ' . $bank->name . ' Bank ' . $bank->bank . ' Account Number ' . $bank->account_number . ' disable');
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return redirect()->route('banks.index')
            ->with('success', 'Account ' . $bank->name . ' - ' . $bank->bank . ' - ' . $bank->account_number . ' deleted successfully.');
    }
}
