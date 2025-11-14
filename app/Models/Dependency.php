<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dependency extends Model
{
    use HasFactory;
    
    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'region_id',
    ];
    
    /**
     * Obtiene la región a la que pertenece esta dependencia.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
    
    /**
     * Obtiene las sedes que pertenecen a esta dependencia.
     */
    public function headquarters(): HasMany
    {
        return $this->hasMany(Headquarters::class);
    }
}
