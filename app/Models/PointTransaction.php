<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    use HasFactory;
    protected $table = 'point_transactions';
    protected $primaryKey = 'transaction_id';
    protected $fillable = [
        'user_id',
        'available_points',
        'transaction_amount'
    ];
}
