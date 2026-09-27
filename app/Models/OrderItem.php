<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model { public $timestamps=false; protected $guarded=[]; public function variant(){return $this->belongsTo(ProductVariant::class,'variant_id');} }
