<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminChat extends Model
{
    use HasFactory;
    protected $table = 'admin_chats';
    protected $primaryKey = 'chat_id';
    protected $fillable = ['message','msg_to','msg_from','seen_flag'];
}
