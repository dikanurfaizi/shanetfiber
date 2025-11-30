<?php

namespace App\Models\Expenses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Budget extends Model
{
    protected $fillable = [
        'expense_category_id',
        'user_id',
        'budget_name',
        'period_month',
        'period_year',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_month' => 'integer',
        'period_year' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Hitung realisasi pengeluaran kategori ini */
    public function actualExpense(): float
    {
        return Expense::where('expense_category_id', $this->expense_category_id)
            ->whereYear('expense_date', $this->period_year)
            ->whereMonth('expense_date', $this->period_month)
            ->sum('amount');
    }
}
