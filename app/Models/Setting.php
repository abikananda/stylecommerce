<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Setting extends Model { public $timestamps=false; protected $primaryKey='key'; public $incrementing=false; protected $keyType='string'; protected $guarded=[]; public static function valueOf(string $key, mixed $default=null):mixed { try { return static::whereKey($key)->value('value') ?? $default; } catch (\Illuminate\Database\QueryException $e) { return $default; } } }
