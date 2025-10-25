<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InvestorAdmin;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit', 'update', 'enable', 'disable']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy', 'delete']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {
        $users = User::orderBy('id', 'DESC')->paginate(5);
        return view('users.index', compact('users'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $roles = Role::pluck('name', 'name')->all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required',
            'contact' => 'required|numeric|regex:/^8[0-9]{7,}$/',
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $input['contact'] = '62' . $request->contact;

        DB::beginTransaction();

        try {
            // 🔹 1. Create user
            $user = User::create($input);

            // 🔹 2. Assign role
            $user->assignRole($request->input('roles'));

            // 🔹 3. Optional: link investor-admin relation
            if (isset($input['can_view_related_users']) && $input['can_view_related_users'] === 'on') {
                foreach ($input['linked_user_ids'] ?? [] as $linkedUserId) {
                    InvestorAdmin::create([
                        'investor_id' => $user->id,
                        'admin_id' => $linkedUserId,
                        'linked_at' => now(),
                        'created_by' => auth()->id(),
                    ]);

                    LogHelper::logInfo(null, [
                        'investor_id' => $user->id,
                        'admin_id' => $linkedUserId,
                    ], 201, 'Linked investor to admin user');
                }
            }

            // 🔹 4. Logging sukses
            LogHelper::logInfo(null, [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ], 201, 'Created new user');

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User created successfully');
        } catch (\Throwable $e) {
            DB::rollBack();

            LogHelper::logError($e, [
                'context' => 'User creation failed, rollback executed',
                'input' => $input,
            ], 500, 'Rollback user creation');

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $user = User::find($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $user = User::find($id);
        $user->contact = preg_replace('/^62/', '', $user->contact); // Hilangkan prefix 62
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();
        $investorAdmins = InvestorAdmin::where('investor_id', $id)->pluck('admin_id')->toArray();

        return view('users.edit', compact('user', 'roles', 'userRole', 'investorAdmins'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'same:confirm-password',
            'roles' => 'required',
            'contact' => 'required|numeric|regex:/^8[0-9]{7,}$/',
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, array('password'));
        }
        $input['contact'] = '62' . $request->contact;

        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        $user->assignRole($request->input('roles'));

        $user->investorAdmins()->delete();
        if (isset($input['can_view_related_users']) && $input['can_view_related_users'] === 'on') {
            foreach ($input['linked_user_ids'] as $linkedUserId) {
                InvestorAdmin::create([
                    'investor_id' => $user->id,
                    'admin_id' => $linkedUserId,
                    'linked_at' => now(),
                    'created_by' => auth()->user()->id,
                ]);
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    public function enable(User $user)
    {
        $user->update([
            'disabled' => 'false',
        ]);
        return redirect()->back()->with('success', 'User ' . $user->name . ' enable');
    }

    public function disable(User $user)
    {
        $user->update([
            'disabled' => 'true',
        ]);

        return redirect()->back()->with('success', 'User ' . $user->name . ' disable');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(User $user)
    {
        return view('users.delete', compact('user'));
    }
    public function destroy($id): RedirectResponse
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }

    public function getLinkableUsers(Request $request)
    {
        $roleName = $request->query('role');

        // Ambil user lain (selain yang memiliki role ini)
        $users = User::select('id', 'name')
            ->with('roles:id,name')
            ->whereHas('roles', fn($q) => $q->where('name', '!=', $roleName))
            ->orderBy('name')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'roleName' => optional($user->roles->first())->name ?? '-', // aman kalau gak punya role
            ]);

        LogHelper::logInfo(null, [
            'roleName' => $roleName,
            'count' => $users->count(),
            'sample' => $users->take(3),
        ], 200, 'Fetched linkable users');

        return response()->json($users);
    }
}
