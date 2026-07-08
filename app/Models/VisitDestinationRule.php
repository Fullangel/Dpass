<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitDestinationRule extends Model
{
    public const TYPE_EMPLOYEE = 'employee_id';
    public const TYPE_DEPARTMENT = 'department_id';
    public const TYPE_DESIGNATION = 'designation_id';

    protected $guarded = ['id'];

    public function visitDestination(): BelongsTo
    {
        return $this->belongsTo(VisitDestination::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::ACTIVE);
    }
}
