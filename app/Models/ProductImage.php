<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductImage extends Model {
    protected $guarded=[];
    public function product(){return $this->belongsTo(Product::class);}
    public function url(): string {
        if (str_starts_with($this->path,'demo/')) {
            $file=public_path('images/demo/'.basename($this->path));
            if (is_file($file)) return asset('images/demo/'.basename($this->path));
        }
        return asset('storage/'.$this->path);
    }
}
