<?php

namespace App\Http\Controllers\Package;

use App\Helpers\LogHelper;
use App\Models\Packages\Package;
use Illuminate\Http\Request;
use App\Http\Requests\PackageStoreRequest;
use App\Http\Requests\PackageUpdateRequest;
use App\Http\Controllers\Controller;
use App\Services\Packages\PackageService;
use App\Services\Packages\PackageQueryService;

class PackageController extends Controller
{
    protected $packageService;
    protected $queryService;

    public function __construct(PackageService $packageService, PackageQueryService $queryService)
    {
        $this->packageService = $packageService;
        $this->queryService = $queryService;
        $this->middleware('permission:package-list|package-create|package-edit|package-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:package-create', ['only' => ['create', 'store', 'setting', 'exportToRouter']]);
        $this->middleware('permission:package-edit', ['only' => ['edit', 'update', 'setting', 'exportToRouter', 'enable', 'disable', 'toggleDisplay']]);
        $this->middleware('permission:package-delete', ['only' => ['destroy', 'delete']]);
    }

    public function index()
    {
        $packages = $this->queryService->getIndexData();
        return view('packages.index', ['packages' => $packages])
            ->with('i', (request()->input('page', 1) - 1) * 20);
    }

    public function create()
    {
        $data = $this->queryService->getCreateFormData();
        return view('packages.create', $data);
    }

    public function store(PackageStoreRequest $request)
    {
        $this->packageService->store($request);
        return redirect()->route('packages.index')->with('success', 'Package created successfully.');
    }

    public function show($slug)
    {
        try {
            $data = $this->packageService->show($slug);
            return view('packages.show', $data);
        } catch (\Exception $e) {
            return redirect()->route('packages.index')
                ->withErrors(['msg' => 'Failed to load package detail.']);
        }
    }

    public function edit($slug)
    {
        $data = $this->queryService->getEditData($slug);
        return view('packages.edit', $data);
    }

    public function update(PackageUpdateRequest $request, $slug)
    {
        $this->packageService->update($request, $slug);
        return redirect()->route('packages.index')->with('success', 'Package updated successfully.');
    }

    public function enable($slug)
    {
        $this->packageService->enable($slug);
        return redirect()->back()->with('success', 'Package enabled successfully.');
    }

    public function disable($slug)
    {
        $this->packageService->disable($slug);
        return redirect()->back()->with('success', 'Package disabled successfully.');
    }

    public function delete($slug)
    {
        $this->packageService->destroy($slug);
        return redirect()->route('packages.index')->with('success', 'Package deleted successfully.');
    }

    public function toggleDisplay(Request $request, $slug)
    {
        $package = $this->packageService->toggleDisplay($slug, $request->boolean('display_to_customer'));
        return response()->json([
            'status'  => 'success',
            'title'   => 'Package Display',
            'message' => 'Display status updated.',
            'value'   => $package->display_to_customer
        ]);
    }
}
