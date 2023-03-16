<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userlist extends Model
{
    use HasFactory;
    protected $table = "userlists";
    protected $primaryKey = "user_id";
}
