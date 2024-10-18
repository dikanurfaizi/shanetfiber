<?php

namespace App\Http\Controllers;

use App\Models\MerkRouter;
use App\Models\TypeRouter;
use Illuminate\Http\Request;

class RouterController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:server-list|server-create|server-edit|server-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:server-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:server-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:server-delete', ['only' => ['destroy', 'delete']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $column = MerkRouter::get('name');
        $routers = TypeRouter::where('disabled', 'false')->orderBy('name', 'ASC')->get();
        return view('routers.index', compact('routers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $merkRouters = MerkRouter::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');
        return view('routers.create', compact('merkRouters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                // 'customer_id' => 'required|unique:billings,customer_id,NULL,name,profile,' . $month,
                // 'name' => 'required|unique:type_routers,name',
                'name' => 'required|unique:type_routers,name,null,null,merk_router_id,' . $request->merk_router_id,
                'merk_router_id' => 'required'
            ],

            [
                'name.unique' => 'Router type ' . $request->name . ' already exist.',
                'merk_router_id.required' => 'Please select brand router'

            ]
        );
        TypeRouter::create($request->all());

        return redirect()->route('routers.index')
            ->with('success', 'Router created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TypeRouter $router)
    {
        return view('routers.edit', compact('router'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TypeRouter $router)
    {
        $this->validate(
            $request,
            [
                'name' => 'required|unique:type_routers,name,' . $router->id . ',id,merk_router_id,' . $router->merk_router_id,
            ],
            [
                'name.unique' => 'Router type ' . $request->name . ' already exist in this brand.',
            ]
        );
        $router->update([
            'name' => $request->name,
        ]);
        return redirect()->route('routers.index')
            ->with('success', 'Router updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(TypeRouter $router)
    {
        return view('routers.delete', compact('router'));
    }
    public function destroy(TypeRouter $router)
    {
        $router->delete();
        return redirect()->route('routers.index')
            ->with('success', 'Router ' . $router->merk_router->name . ' ' . $router->name . ' deleted successfully.');
    }
}
