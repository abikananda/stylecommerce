<?php
namespace App\Services;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class Cart {
    public function token(Request $request): string {
        if ($request->user()) return 'user:'.$request->user()->id;
        $token=$request->session()->get('cart_token');
        if (!$token) { $token=Str::random(48); $request->session()->put('cart_token',$token); }
        return $token;
    }
    public function items(Request $request) { return CartItem::with('variant.product.images')->where('cart_token',$this->token($request))->get(); }
    public function merge(Request $request, int $userId): void {
        $guest=$request->session()->pull('cart_token'); if (!$guest) return;
        foreach (CartItem::where('cart_token',$guest)->get() as $item) {
            $existing=CartItem::where('cart_token','user:'.$userId)->where('variant_id',$item->variant_id)->first();
            if ($existing) { $existing->update(['quantity'=>min(20,$existing->quantity+$item->quantity)]); $item->delete(); }
            else $item->update(['cart_token'=>'user:'.$userId]);
        }
    }
}
