<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adds extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['add_status', 'view_count'];
    protected $table = 'adds';
    protected $primaryKey = 'add_id';
}
