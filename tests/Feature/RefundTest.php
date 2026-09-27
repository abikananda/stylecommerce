<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{Order,PaymentAttempt,Refund};
use App\Services\Refunds;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
class RefundTest extends TestCase {
    use RefreshDatabase;
    public function test_full_refund_is_idempotent_and_only_marked_processed_after_gateway_confirmation():void {
        Mail::fake();
        $order=Order::create(['checkout_token'=>'1948ebdb-3e2a-4c22-a3c7-4938fd368e5a','email'=>'buyer@example.com','shipping_address'=>[],'subtotal_paise'=>150000,'total_paise'=>150000,'gateway_order_id'=>'order_1','status'=>'paid']);
        PaymentAttempt::create(['order_id'=>$order->id,'gateway_order_id'=>'order_1','gateway_payment_id'=>'pay_1','status'=>'captured']);
        Http::fake(['api.razorpay.com/v1/payments/pay_1/refund'=>Http::response(['id'=>'rfnd_1','payment_id'=>'pay_1','amount'=>150000,'currency'=>'INR','status'=>'pending'],200),'api.razorpay.com/v1/refunds/rfnd_1'=>Http::response(['id'=>'rfnd_1','payment_id'=>'pay_1','amount'=>150000,'status'=>'processed'],200)]);
        app(Refunds::class)->initiate($order);
        $this->assertSame('refund_pending',$order->fresh()->status);
        app(Refunds::class)->sync('rfnd_1');app(Refunds::class)->sync('rfnd_1');
        $this->assertSame('refunded',$order->fresh()->status);$this->assertSame(1,Refund::count());Http::assertSentCount(3);
    }
}
