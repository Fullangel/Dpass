<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $guarded = ['id'];
    protected $fillable = [
        'name','status','headquarters_id'
    ];
    protected $fakeColumns = [];
    public $timestamps = false;

    public function headquarters()
    {
        return $this->belongsTo(Headquarters::class);
    }
}
