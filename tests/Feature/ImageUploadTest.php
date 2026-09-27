<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{Category,Product,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
class ImageUploadTest extends TestCase {
    use RefreshDatabase;
    public function test_admin_upload_is_resized_and_saved_as_webp():void {
        Storage::fake('public');$category=Category::create(['name'=>'Earrings','slug'=>'earrings']);$product=Product::create(['category_id'=>$category->id,'name'=>'Pearl drops','slug'=>'pearl-drops','description'=>'Sample','published'=>true]);
        $admin=User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>'password']);$admin->forceFill(['is_admin'=>true])->save();
        $this->actingAs($admin)->post('/admin/products/'.$product->slug.'/images',['images'=>[UploadedFile::fake()->image('earrings.jpg',2400,1200)],'alt'=>'Pearl earrings on velvet'])->assertRedirect();
        $image=$product->images()->firstOrFail();$this->assertStringEndsWith('.webp',$image->path);Storage::disk('public')->assertExists($image->path);
        $size=getimagesize(Storage::disk('public')->path($image->path));$this->assertLessThanOrEqual(1600,$size[0]);
    }
}
