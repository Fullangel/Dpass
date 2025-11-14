<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Headquarters extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'headquarters';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'address',
        'phone',
        'region_id',
        'dependency_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the region that owns the headquarters.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the dependency that owns the headquarters.
     */
    public function dependency(): BelongsTo
    {
        return $this->belongsTo(Dependency::class);
    }

    /**
     * Get the departments for the headquarters.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get the visiting details for the headquarters.
     */
    public function visitingDetails(): HasMany
    {
        return $this->hasMany(VisitingDetails::class);
    }

    /**
     * Get the attendances for the headquarters.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the bookings for the headquarters.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}