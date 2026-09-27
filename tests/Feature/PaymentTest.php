<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{Category,Product,ProductVariant,Order,PaymentAttempt};
use App\Services\Payments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
class PaymentTest extends TestCase {
    use RefreshDatabase;
    public function test_captured_payment_confirms_once_and_releases_reservation():void {
        Mail::fake();$category=Category::create(['name'=>'Earrings','slug'=>'earrings']);$product=Product::create(['category_id'=>$category->id,'name'=>'Pearl drops','slug'=>'pearl-drops','description'=>'Earrings','published'=>true]);
        $variant=ProductVariant::create(['product_id'=>$product->id,'sku'=>'PEARL','stock'=>3,'reserved'=>1,'price_paise'=>150000]);
        $order=Order::create(['checkout_token'=>'1948ebdb-3e2a-4c22-a3c7-4938fd368e5a','email'=>'buyer@example.com','shipping_address'=>[],'subtotal_paise'=>150000,'total_paise'=>150000,'gateway_order_id'=>'order_1','reservation_expires_at'=>now()->addMinutes(15)]);
        $order->items()->create(['variant_id'=>$variant->id,'sku'=>'PEARL','name'=>'Pearl drops','unit_price_paise'=>150000,'quantity'=>1]);
        PaymentAttempt::create(['order_id'=>$order->id,'gateway_order_id'=>'order_1']);
        Http::fake(['api.razorpay.com/v1/payments/pay_1'=>Http::response(['id'=>'pay_1','order_id'=>'order_1','amount'=>150000,'currency'=>'INR','status'=>'captured'],200)]);
        $this->assertTrue(app(Payments::class)->confirmCaptured($order,'pay_1'));
        $this->assertTrue(app(Payments::class)->confirmCaptured($order,'pay_1'));
        $this->assertEquals('paid',$order->fresh()->status);$this->assertEquals(2,$variant->fresh()->stock);$this->assertEquals(0,$variant->fresh()->reserved);
    }
    public function test_invalid_webhook_signature_is_rejected():void {$this->postJson('/payments/razorpay/webhook',['event'=>'payment.captured'],['X-Razorpay-Signature'=>'wrong'])->assertForbidden();}
}
