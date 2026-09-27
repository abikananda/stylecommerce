<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{Category,Product,ProductVariant,CartItem,Order,Setting,User};
use App\Services\Checkout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
class CheckoutTest extends TestCase {
    use RefreshDatabase;
    private function variant():ProductVariant { $category=Category::create(['name'=>'Bangles','slug'=>'bangles']);$product=Product::create(['category_id'=>$category->id,'name'=>'Gold bangle','slug'=>'gold-bangle','description'=>'A bangle','published'=>true]);return ProductVariant::create(['product_id'=>$product->id,'sku'=>'BANGLE-24','size'=>'2.4','price_paise'=>125000,'stock'=>2]); }
    private function address():array{return ['name'=>'A Buyer','phone'=>'9876543210','line1'=>'Street 1','city'=>'Pune','state'=>'Maharashtra','pincode'=>'411001'];}
    public function test_checkout_is_idempotent_and_reserves_stock_only_once():void {
        Http::fake(['api.razorpay.com/v1/orders'=>Http::response(['id'=>'order_test','amount'=>125000,'currency'=>'INR'],200)]);
        $variant=$this->variant();CartItem::create(['cart_token'=>'guest','variant_id'=>$variant->id,'quantity'=>1]);
        $checkout=app(Checkout::class);$token='1948ebdb-3e2a-4c22-a3c7-4938fd368e5a';
        $one=$checkout->place('guest',null,$token,'buyer@example.com',$this->address(),null);
        $two=$checkout->place('guest',null,$token,'buyer@example.com',$this->address(),null);
        $this->assertSame($one->id,$two->id);$this->assertSame(1,Order::count());$this->assertSame(1,$variant->fresh()->reserved);Http::assertSentCount(1);
    }
    public function test_stock_cannot_be_oversold():void {
        Http::fake(['api.razorpay.com/v1/orders'=>Http::response(['id'=>'order_test','amount'=>250000,'currency'=>'INR'],200)]);
        $variant=$this->variant();CartItem::create(['cart_token'=>'guest','variant_id'=>$variant->id,'quantity'=>2]);
        app(Checkout::class)->place('guest',null,'1948ebdb-3e2a-4c22-a3c7-4938fd368e5a','buyer@example.com',$this->address(),null);
        CartItem::create(['cart_token'=>'other','variant_id'=>$variant->id,'quantity'=>1]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(Checkout::class)->place('other',null,'5948ebdb-3e2a-4c22-a3c7-4938fd368e5a','other@example.com',$this->address(),null);
    }
    public function test_admin_page_is_forbidden_to_customers():void {$user=User::create(['name'=>'Customer','email'=>'a@example.com','password'=>'password']);$this->actingAs($user)->get('/admin')->assertForbidden();}
}
