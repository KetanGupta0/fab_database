<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $table = 'comments';
    protected $fillable = ['add_id','user_id','owner_id','comment_msg','comment_status','commenter'];
    protected $primaryKey = 'id';
}
