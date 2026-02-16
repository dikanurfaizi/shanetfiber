<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expenses;

class ExpensesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:expense-list')->only(['index', 'show']);
        $this->middleware('permission:expense-create')->only(['create', 'store']);
        $this->middleware('permission:expense-edit')->only(['edit', 'update']);
        $this->middleware('permission:expense-delete')->only(['destroy']);
    }

    public function index()
    {
        $this->authorize('viewAny', Expenses::class);

        $expenses = Expenses::visibleFor(auth()->user())->paginate();

        return view('expenses.index', compact('expenses'));
    }

    public function show(Expenses $expense)
    {
        $this->authorize('view', $expense);

        return view('expenses.show', compact('expense'));
    }

    public function create()
    {
        $this->authorize('create', Expenses::class);

        return view('expenses.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Expenses::class);

        $expense = Expenses::create([
            ...$request->all(),
            'created_by' => auth()->id()
        ]);

        return redirect()->route('expenses.index');
    }

    public function update(Request $request, Expenses $expense)
    {
        $this->authorize('update', $expense);

        $expense->update($request->all());

        return back();
    }

    public function destroy(Expenses $expense)
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return back();
    }
}
