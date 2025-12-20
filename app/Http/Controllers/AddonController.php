<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddonController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:addon-view|addon-create|addon-edit|addon-delete|secret-disable|secret-edit', ['only' => ['index', 'show']]);
        $this->middleware('permission:addon-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:addon-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:addon-disable', ['only' => ['disable']]);
        $this->middleware('permission:addon-enable', ['only' => ['enable']]);
    }

    public function index()
    {
        $addons = Addon::paginate(10);

        $message = $addons->isEmpty() ? "Saat ini belum ada addon list yang dibuat" : null;

        $i = ($addons->currentPage() - 1) * $addons->perPage();

        return view('addons.index', compact('addons', 'message', 'i'));
    }

    public function create()
    {
        return view('addons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'is_recurring' => 'required|boolean',
            'is_active'    => 'required|boolean',
        ]);

        $data['slug']      = Str::slug($data['name']);

        Addon::create($data);

        return redirect()
            ->route('addons.index')
            ->with('success', 'Addon successfully created');
    }

    public function edit(Addon $addon)
    {
        return view('addons.edit', compact('addon'));
    }

    public function update(Request $request, Addon $addon)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'is_recurring' => 'required|boolean',
            'is_active'    => 'required|boolean',
        ]);

        $addon->update($data);

        return redirect()
            ->route('addons.index')
            ->with('success', 'Addon successfully updated');
    }

    public function disable(Addon $addon)
    {
        $addon->update(['is_active' => false]);

        return back()->with('success', 'Addon disabled');
    }

    public function enable(Addon $addon)
    {
        $addon->update(['is_active' => true]);

        return back()->with('success', 'Addon enabled');
    }
}
