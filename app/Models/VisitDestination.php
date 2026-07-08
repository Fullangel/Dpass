<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitDestination extends Model
{
    protected $guarded = ['id'];

    public function headquarters(): BelongsTo
    {
        return $this->belongsTo(Headquarters::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(VisitDestinationRule::class);
    }

    public function visitingDetails(): HasMany
    {
        return $this->hasMany(VisitingDetails::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_visit_destinations')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::ACTIVE);
    }
}
