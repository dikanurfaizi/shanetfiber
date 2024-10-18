<?php

namespace App\Http\Controllers;

use App\Models\MerkRouter;
//use App\Models\TypeRouter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:server-list|server-create|server-edit|server-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:server-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:server-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:server-delete', ['only' => ['destroy', 'delete']]);
    }
    public function index()
    {
        $brands = MerkRouter::where('disabled', 'false')->orderBy('name', 'ASC')->get();
        return view('routers.index_brand', compact('brands'));
    }
    public function create()
    {
        return view('routers.create_brand');
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => 'required|unique:merk_routers,name',
            ],

            [
                'name.unique' => 'Router brand ' . $request->name . ' already exist.',

            ]
        );
        MerkRouter::create($request->all());

        return redirect()->route('brand.index')
            ->with('success', 'Router brand ' . $request->name . ' created successfully.');
    }


    public function edit(MerkRouter $brand)
    {
        return view('routers.edit_brand', compact('brand'));
    }

    public function update(Request $request, MerkRouter $brand)
    {
        $this->validate(
            $request,
            [
                'name' => ['required', 'string', Rule::unique('merk_routers')->ignore($brand->id, 'id')],
            ],
            [
                'name.unique' => 'Router brand ' . $request->name . ' already exist.',
            ]
        );
        $brand->update([
            'name' => $request->name,
        ]);
        return redirect()->route('brand.index')
            ->with('success', 'Brand router updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(MerkRouter $brand)
    {
        return view('routers.delete_brand', compact('brand'));
    }
    public function destroy(MerkRouter $brand)
    {
        $brand->delete();
        return redirect()->route('brand.index')
            ->with('success', 'Router brand ' . $brand->name . ' deleted successfully.');
    }
}
