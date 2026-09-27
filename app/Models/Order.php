<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model { protected $guarded=[]; protected function casts():array{return ['shipping_address'=>'array','reservation_expires_at'=>'datetime'];} public function items(){return $this->hasMany(OrderItem::class);} public function attempts(){return $this->hasMany(PaymentAttempt::class);} public function events(){return $this->hasMany(OrderEvent::class);} public function user(){return $this->belongsTo(User::class);} public function coupon(){return $this->belongsTo(Coupon::class);} }
