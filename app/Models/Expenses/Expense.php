<?php

namespace App\Models\Expenses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_category_id',
        'user_id',
        'title',
        'description',
        'amount',
        'expense_date',
        'expense_time',
        'reference_no',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'expense_time' => 'datetime:H:i',
        'amount' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseItem::class, 'expense_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ExpenseAttachment::class, 'expense_id');
    }

    /** 
     * Auto-sum total item untuk sinkron ke kolom amount 
     */
    public function recalcTotal()
    {
        $this->amount = $this->items()->sum('total');
        $this->save();
    }
}
