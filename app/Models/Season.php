<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = ['hotel_id', 'contract_type', 'name', 'code', 'start_date', 'end_date', 'active'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'active' => 'boolean'];

    /** The single source of truth for every current-period calculation. */
    public function scopeCurrent($query)
    {
        return $query->where('active', true)->whereDate('end_date', '>=', today());
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
