<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSelect extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'form_select_data';
    protected $primaryKey = 'form_select_id';
}
