<?php
namespace App\Services;
use App\Models\Coupon;
use App\Models\Setting;
use Illuminate\Validation\ValidationException;
class Pricing {
    public function calculate($items, ?Coupon $coupon, string $pincode): array {
        if (!preg_match('/^[1-9][0-9]{5}$/',$pincode)) throw ValidationException::withMessages(['pincode'=>'Enter a valid six-digit Indian PIN code.']);
        $prefixes=array_filter(array_map('trim',explode(',',Setting::valueOf('shipping_pincode_prefixes',''))));
        if ($prefixes && !collect($prefixes)->contains(fn($prefix)=>str_starts_with($pincode,$prefix))) throw ValidationException::withMessages(['pincode'=>'Delivery is not available at this PIN code.']);
        $subtotal=0;
        foreach ($items as $item) $subtotal += $item->variant->price_paise*$item->quantity;
        if ($subtotal===0) throw ValidationException::withMessages(['cart'=>'Your cart is empty.']);
        $discount=0;
        if ($coupon) {
            if (!$coupon->active || ($coupon->expires_at && $coupon->expires_at->isPast()) || ($coupon->usage_limit!==null && $coupon->used >= $coupon->usage_limit) || $subtotal < $coupon->min_order_paise) throw ValidationException::withMessages(['coupon'=>'This coupon is unavailable for this order.']);
            $discount=$coupon->type==='percent' ? intdiv($subtotal*$coupon->value,100) : $coupon->value;
            $discount=min($subtotal,$discount);
        }
        $shipping=($subtotal-$discount)>= (int) Setting::valueOf('free_shipping_threshold_paise','99900') ? 0 : (int) Setting::valueOf('shipping_paise','9900');
        $tax=intdiv(($subtotal-$discount)*(int) Setting::valueOf('tax_rate_bps','0')+5000,10000);
        return ['subtotal_paise'=>$subtotal,'discount_paise'=>$discount,'shipping_paise'=>$shipping,'tax_paise'=>$tax,'total_paise'=>$subtotal-$discount+$shipping+$tax];
    }
}
