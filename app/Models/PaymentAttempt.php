<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentAttempt extends Model { protected $guarded=[]; protected function casts():array{return ['response'=>'array'];} }
