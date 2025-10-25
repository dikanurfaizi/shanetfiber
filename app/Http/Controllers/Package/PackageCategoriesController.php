<?php

namespace App\Http\Controllers\Package;

use App\Http\Controllers\Controller;
use App\Models\Packages\PackageCategories;
use App\Helpers\LogHelper;
use App\Http\Requests\StorePackageCategoryRequest;
use App\Http\Requests\UpdatePackageCategoryRequest;
use Illuminate\Support\Str;

class PackageCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $package_categories = PackageCategories::paginate(10); // 10 items per page
        return view('packages.package-categories.index', [
            'package_categories' => $package_categories,
            'i' => (request()->input('page', 1) - 1) * 10
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('packages.package-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePackageCategoryRequest $request)
    {
        $traceId = (string) Str::uuid();

        $data = $request->validated();

        $slug = lcfirst(str_replace(' ', '', ucwords(strtolower($request->name)))) . Str::random(4);
        $data['slug'] = $slug;

        PackageCategories::create($data);

        LogHelper::logInfo(null, [
            'package' => $data
        ], 200, 'Successfully created new package category', $traceId);

        return redirect()->route('categories.index')
            ->with('success', 'Package category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PackageCategories $package_category)
    {
        $traceId = (string) Str::uuid();
        LogHelper::logInfo(null, [
            'package' => $package_category
        ], 200, 'Successfully show package category', $traceId);
        return view('packages.package-categories.show', ['package_category' => $package_category]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PackageCategories $package_category)
    {
        $traceId = (string) Str::uuid();

        try {
            LogHelper::logInfo(null, [
                'category' => $package_category->only(['id', 'name', 'description']),
            ], 200, 'Successfully show category', $traceId);

            return view('packages.package-categories.edit', compact('package_category'));
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'category_id' => $package_category->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
            ], 500, 'Failed to show category', $traceId);

            return redirect()->back()
                ->withErrors(['msg' => 'Failed to load category data. Please try again.']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePackageCategoryRequest $request, PackageCategories $package_category)
    {
        $traceId = (string) Str::uuid();

        try {
            $validated = $request->validated();

            $package_category->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            LogHelper::logInfo($request, [
                'category_id' => $package_category->id,
                'changes' => $package_category->getChanges()
            ], 200, 'Category updated successfully', $traceId);

            return redirect()->route('categories.index')
                ->with('success', 'Category updated successfully');
        } catch (\Exception $e) {
            LogHelper::logError($request, [
                'category_id' => $package_category->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'input_data' => $request->except('_token', '_method')
            ], 500, 'Failed to update category', $traceId);

            return redirect()->back()
                ->withInput()
                ->withErrors(['msg' => 'Failed to update category. Please try again.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $traceId = (string) Str::uuid();
        $package_category = PackageCategories::withTrashed()->findOrFail($id);

        try {
            // Jika kategori sudah di soft delete
            if ($package_category->trashed()) {
                return redirect()->back()
                    ->with('info', 'Kategori sudah dinonaktifkan sebelumnya');
            }

            // Cek relasi package
            if ($package_category->packages()->exists()) {
                $packetCount = $package_category->packages()->count();

                LogHelper::logWarning(null, [
                    'category_id' => $id,
                    'packet_count' => $packetCount
                ], 422, 'Disable prevented - category in use', $traceId);

                return response()->json([
                    'success' => false,
                    'message' => "Cannot be disabled because it is used by $packetCount package"
                ], 403);
            }

            // Soft delete
            $package_category->delete();

            LogHelper::logInfo(null, [
                'category_id' => $id,
                'action' => 'soft_delete'
            ], 200, 'Category disabled successfully', $traceId);

            return response()->json([
                'success' => true,
                'message' => 'Category disabled successfully'
            ]);
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'category_id' => $id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
            ], 500, 'Failed to disable category', $traceId);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disable category'
            ], 500);
        }
    }

    public function activate(PackageCategories $package_category)
    {
        $traceId = (string) Str::uuid();

        try {
            if (!$package_category->is_active) {
                $package_category->update(['is_active' => true]);

                LogHelper::logInfo(null, [
                    'package' => $package_category,
                    'action' => 'activate'
                ], 200, 'Successfully enabled package category', $traceId);
            }

            return redirect()->back()->with('success', 'Package category enabled successfully.');
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'package_id' => $package_category->id,
                'error' => $e->getMessage()
            ], 500, 'Failed to activate package category', $traceId);

            return redirect()->back()->with('error', 'Failed to enable package category.');
        }
    }


    public function deactivate(PackageCategories $package_category)
    {
        $traceId = (string) Str::uuid();

        try {
            if ($package_category->is_active) {
                $package_category->update(['is_active' => false]);

                LogHelper::logInfo(null, [
                    'package' => $package_category,
                    'action' => 'deactivate'
                ], 200, 'Successfully disabled package category', $traceId);
            }

            return redirect()->back()->with('success', 'Package category disabled successfully.');
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'package_id' => $package_category->id,
                'error' => $e->getMessage()
            ], 500, 'Failed to deactivate package category', $traceId);

            return redirect()->back()->with('error', 'Failed to disable package category.');
        }
    }
}
