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

    // Define the categories relationship
    public function categories(){
        return $this->belongsToMany(Category::class, 'categories', 'add_id', 'cid');
    }

    // Define the hasManyThrough relationship to access the parent categories
    public function parentCategories(){
        return $this->hasManyThrough(Category::class, 'category_relations', 'child_id', 'cid', 'id', 'parent_id');
    }



}
