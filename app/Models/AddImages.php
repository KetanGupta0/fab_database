<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddImages extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'add_images';
    protected $primaryKey = 'image_id';
    protected $fillable = ['add_id', 'user_id', 'image_name' ];
}
