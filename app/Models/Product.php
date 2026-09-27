<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {
    protected $guarded=[];
    protected function casts(): array { return ['published'=>'boolean']; }
    public function category(){return $this->belongsTo(Category::class);}
    public function collection(){return $this->belongsTo(Collection::class);}
    public function variants(){return $this->hasMany(ProductVariant::class);}
    public function images(){return $this->hasMany(ProductImage::class)->orderBy('position');}
    public function getRouteKeyName(): string {return 'slug';}
}
