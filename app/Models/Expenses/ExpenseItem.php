<?php
// App\Models\ExpenseItem.php
namespace App\Models\Expenses;

use Illuminate\Database\Eloquent\Model;
use App\Models\Mikrotik;

class ExpenseItem extends Model
{
    protected $fillable = [
        'expense_id',
        'mikrotik_id',
        'item_name',
        'qty',
        'price'
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function mikrotik()
    {
        return $this->belongsTo(Mikrotik::class);
    }
}
