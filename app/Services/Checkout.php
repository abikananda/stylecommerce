<?php
namespace App\Services;
use App\Models\{CartItem,Coupon,Order,OrderEvent,PaymentAttempt,ProductVariant,Setting};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class Checkout {
    public function __construct(private Payments $payments, private Pricing $pricing) {}
    public function place(string $cartToken, ?int $userId, string $checkoutToken, string $email, array $address, ?string $couponCode, string $paymentMethod='razorpay'): Order {
        return DB::transaction(function () use ($cartToken,$userId,$checkoutToken,$email,$address,$couponCode,$paymentMethod) {
            $existing=Order::where('checkout_token',$checkoutToken)->lockForUpdate()->first();
            if ($existing) return $existing;
            $items=CartItem::where('cart_token',$cartToken)->orderBy('variant_id')->get();
            if ($items->isEmpty()) throw ValidationException::withMessages(['cart'=>'Your cart is empty.']);
            foreach ($items as $item) {
                $item->setRelation('variant',ProductVariant::with('product')->whereKey($item->variant_id)->lockForUpdate()->firstOrFail());
                if (!$item->variant->product->published || $item->quantity > $item->variant->available()) throw ValidationException::withMessages(['cart'=>'An item is out of stock. Please review your cart.']);
            }
            $coupon=$couponCode ? Coupon::where('code',strtoupper($couponCode))->lockForUpdate()->first() : null;
            if ($couponCode && !$coupon) throw ValidationException::withMessages(['coupon'=>'Coupon not found.']);
            if ($coupon && $coupon->usage_limit!==null && $coupon->used+Order::where('coupon_id',$coupon->id)->where('status','pending_payment')->where('reservation_expires_at','>',now())->count()>=$coupon->usage_limit) throw ValidationException::withMessages(['coupon'=>'This coupon has reached its usage limit.']);
            if (!in_array($paymentMethod,['razorpay','cod'],true) || ($paymentMethod==='cod' && Setting::valueOf('cod_enabled','0')!=='1')) throw ValidationException::withMessages(['payment_method'=>'This payment method is unavailable.']);
            $totals=$this->pricing->calculate($items,$coupon,$address['pincode']);
            $order=Order::create(array_merge($totals,['checkout_token'=>$checkoutToken,'cart_token'=>$cartToken,'user_id'=>$userId,'email'=>$email,'shipping_address'=>$address,'coupon_id'=>$coupon?->id,'payment_method'=>$paymentMethod,'status'=>$paymentMethod==='cod'?'processing':'pending_payment','reservation_expires_at'=>$paymentMethod==='cod'?null:now()->addMinutes(15)]));
            foreach ($items as $item) {
                $variant=$item->variant;
                $order->items()->create(['variant_id'=>$variant->id,'sku'=>$variant->sku,'name'=>$variant->product->name,'options'=>implode(' / ',array_filter([$variant->size,$variant->colour,$variant->material,$variant->set_size])),'unit_price_paise'=>$variant->price_paise,'quantity'=>$item->quantity]);
                if ($paymentMethod==='cod') $variant->decrement('stock',$item->quantity);
                else $variant->increment('reserved',$item->quantity);
            }
            if ($paymentMethod==='cod') {
                if ($coupon) $coupon->increment('used');
                foreach ($items as $item) $item->delete();
                OrderEvent::create(['order_id'=>$order->id,'to_status'=>'processing','note'=>'Cash on delivery; payment due on delivery']);
                DB::afterCommit(fn()=>\Illuminate\Support\Facades\Mail::to($order->email)->queue(new \App\Mail\OrderUpdate($order->id)));
                return $order;
            }
            // Keep the row lock while creating the gateway order so concurrent retries share one attempt.
            $gateway=$this->payments->create($order);
            if (($gateway['amount']??null)!==$order->total_paise || ($gateway['currency']??null)!=='INR') throw new \RuntimeException('Gateway amount mismatch');
            $order->update(['gateway_order_id'=>$gateway['id']]);
            PaymentAttempt::create(['order_id'=>$order->id,'gateway_order_id'=>$gateway['id']]);
            OrderEvent::create(['order_id'=>$order->id,'to_status'=>'pending_payment']);
            return $order;
        },3);
    }
}
