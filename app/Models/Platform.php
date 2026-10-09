<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Platform extends Model { protected $fillable=['name','label','code','platform_group','description','active']; public function users(){return $this->belongsToMany(User::class);} }
