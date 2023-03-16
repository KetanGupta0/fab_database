<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fields extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'form_fields';
    protected $primaryKey = 'form_field_id';
}
