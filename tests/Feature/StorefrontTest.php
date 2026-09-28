<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{Product,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
class StorefrontTest extends TestCase {
    use RefreshDatabase;
    protected function setUp():void {parent::setUp();$this->withoutVite();$this->seed();}
    public function test_seeded_storefront_and_product_pages_render():void {
        $this->get('/')->assertOk()->assertSee('AURÉ Jewellery');
        $this->get('/shop?category=bangles&available=1')->assertOk()->assertSee('Nira Gold Bangles');
        $this->get('/products/nira-gold-bangles')->assertOk()->assertSee('Choose an option');
        $this->assertSame(6,Product::whereHas('images')->count());
        $this->assertTrue(Storage::disk('public')->exists('demo/nira-gold-bangles.webp'));
        $this->get('/products/nira-gold-bangles')->assertSee('Three slim polished gold-toned bangles');
    }
    public function test_existing_demo_products_show_bundled_photo_and_gain_image_record_on_reseed():void {
        $product=Product::where('slug','nira-gold-bangles')->firstOrFail();
        $product->images()->delete();
        $this->get('/shop?category=bangles')->assertOk()->assertSee('/images/demo/nira-gold-bangles.webp');
        $this->get('/products/nira-gold-bangles')->assertOk()->assertSee('/images/demo/nira-gold-bangles.webp');
        $this->seed();
        $this->assertSame(1,$product->images()->count());
    }
    public function test_admin_product_form_and_order_list_render():void {
        $admin=User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>'password']);$admin->forceFill(['is_admin'=>true])->save();
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/products/'.Product::first()->slug.'/edit')->assertOk();
        $this->actingAs($admin)->get('/admin/orders')->assertOk();
        $this->actingAs($admin)->get('/admin/customers')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
    }
}
