<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'amount',
        'remain_amount',
        'total_terms',
        'remain_terms',
        'user_id',
        'status'
    ];
     
    public function scheduledRepayments()
    {
        return $this->hasMany(ScheduledRepayment::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
