<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $table = 'designations';
    protected $guarded = ['id'];
    protected $fillable = [
        'name','status','headquarters_id'
    ];
    protected $fakeColumns = [];

    public $timestamps = false;

    protected $casts = [ 
        
    ];

    public function headquarters()
    {
        return $this->belongsTo(Headquarters::class);
    }
}
