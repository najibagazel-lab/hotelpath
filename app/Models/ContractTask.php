<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ContractTask extends Model { protected $fillable=['contract_id','platform_id','assigned_user_id','is_active','status','completed_at','completed_by']; protected $casts=['is_active'=>'boolean','completed_at'=>'datetime']; public function contract(){return $this->belongsTo(Contract::class);} public function platform(){return $this->belongsTo(Platform::class);} public function assignee(){return $this->belongsTo(User::class,'assigned_user_id');} }
