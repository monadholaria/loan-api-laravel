<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledRepayment extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'amount',
        'loan_id',
        'repayment_at',
        'amount_to_pay',
        'amount_paid',
        'status'
    ];
    
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
    
}
