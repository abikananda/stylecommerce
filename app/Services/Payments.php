<?php
namespace App\Services;
use App\Models\{Order,CartItem};
use App\Models\OrderEvent;
use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderUpdate;
use Illuminate\Validation\ValidationException;
class Payments {
    private function client() {
        if (!config('services.razorpay.key_id') || !config('services.razorpay.key_secret')) throw ValidationException::withMessages(['payment'=>'Payment gateway is not configured.']);
        return Http::withBasicAuth(config('services.razorpay.key_id'),config('services.razorpay.key_secret'))->baseUrl('https://api.razorpay.com/v1')->timeout(12)->acceptJson();
    }
    public function create(Order $order): array {
        return $this->client()->post('/orders',['amount'=>$order->total_paise,'currency'=>'INR','receipt'=>'order-'.$order->id,'payment_capture'=>1])->throw()->json();
    }
    public function fetch(string $id): array { return $this->client()->get('/payments/'.rawurlencode($id))->throw()->json(); }
    public function orderPayments(string $id): array { return $this->client()->get('/orders/'.rawurlencode($id).'/payments')->throw()->json('items',[]); }
    public function verifyCallback(Order $order, string $paymentId, string $signature): bool {
        $expected=hash_hmac('sha256',$order->gateway_order_id.'|'.$paymentId,config('services.razorpay.key_secret'));
        return hash_equals($expected,$signature);
    }
    public function verifyWebhook(string $payload, string $signature): bool {
        $secret=config('services.razorpay.webhook_secret');
        return $secret && hash_equals(hash_hmac('sha256',$payload,$secret),$signature);
    }
    public function confirmCaptured(Order $order, string $paymentId): bool {
        $payment=$this->fetch($paymentId);
        if (($payment['status']??'')!=='captured' || ($payment['order_id']??'')!==$order->gateway_order_id || (int)($payment['amount']??0)!==$order->total_paise || ($payment['currency']??'')!=='INR') return false;
        DB::transaction(function () use ($order,$paymentId,$payment) {
            $locked=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status==='paid' || in_array($locked->status,['processing','shipped','delivered'],true)) return;
            if ($locked->status!=='pending_payment') return; // A captured late payment needs manual refund/reconciliation.
            foreach ($locked->items()->orderBy('variant_id')->get() as $item) {
                if (!$item->variant_id) continue;
                $variant=$item->variant()->lockForUpdate()->firstOrFail();
                if ($variant->reserved<$item->quantity || $variant->stock<$item->quantity) throw new \RuntimeException('Stock reservation inconsistency');
                $variant->decrement('reserved',$item->quantity); $variant->decrement('stock',$item->quantity);
            }
            if ($locked->coupon_id) $locked->coupon()->lockForUpdate()->first()?->increment('used');
            foreach ($locked->items as $item) {
                $cart=CartItem::where('cart_token',$locked->cart_token)->where('variant_id',$item->variant_id)->lockForUpdate()->first();
                if ($cart) { $left=$cart->quantity-$item->quantity; $left>0 ? $cart->update(['quantity'=>$left]) : $cart->delete(); }
            }
            $locked->update(['status'=>'paid','reservation_expires_at'=>null]);
            PaymentAttempt::where('gateway_order_id',$locked->gateway_order_id)->update(['status'=>'captured','gateway_payment_id'=>$paymentId,'response'=>$payment]);
            OrderEvent::create(['order_id'=>$locked->id,'from_status'=>'pending_payment','to_status'=>'paid','note'=>'Verified captured payment']);
            DB::afterCommit(fn()=>Mail::to($locked->email)->queue(new OrderUpdate($locked->id)));
        });
        return true;
    }
}
