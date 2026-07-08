<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BackendMenu extends Model
{
    use HasFactory;
    
    protected $table = 'backend_menus';
    
    protected $fillable = [
        'name',
        'link',
        'icon',
        'parent_id',
        'priority',
        'status'
    ];
    
    public $timestamps = false;
}
