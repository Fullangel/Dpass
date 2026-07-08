<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Shipu\Watchable\Traits\HasAuditColumn;

class PreRegister extends Model
{
    use HasAuditColumn;

    protected $table = 'pre_registers';
    protected $guarded = ['id'];
    protected $auditColumn = true;
    protected $fillable = ['headquarters_id'];

    protected $fakeColumns = [];

    public function creator()
    {
        return $this->morphTo();
    }

    public function editor()
    {
        return $this->morphTo();
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function headquarters()
    {
        return $this->belongsTo(Headquarters::class);
    }

}
