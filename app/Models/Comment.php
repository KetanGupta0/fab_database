<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $table = 'comments';
    protected $fillable = [
        'add_id',
        'comment_msg',
        'comment_to',
        'comment_from',
        'seen_flag',
    ];
    protected $primaryKey = 'id';
}
