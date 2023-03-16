<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class formData extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'form_data';
    protected $primaryKey = 'form_data_id';
}
