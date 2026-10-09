<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'contract_id',
        'platform_id',
        'action',
        'details',
        'created_at',
    ];
    public function user() { return $this->belongsTo(User::class); }
    public function contract() { return $this->belongsTo(Contract::class); }
    public function platform() { return $this->belongsTo(Platform::class); }
}
