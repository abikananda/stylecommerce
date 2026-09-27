<?php
namespace App\Http\Controllers;
use App\Models\{CartItem,ProductVariant};
use App\Services\Cart;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
class CartController extends Controller {
    public function index(Request $request,Cart $cart) {return view('store.cart',['items'=>$cart->items($request)]);}
    public function add(Request $request,Cart $cart) {
        $data=$request->validate(['variant_id'=>'required|integer|exists:product_variants,id','quantity'=>'required|integer|min:1|max:20']);
        $variant=ProductVariant::with('product')->findOrFail($data['variant_id']); abort_unless($variant->product->published,404);
        $token=$cart->token($request); $item=CartItem::firstOrNew(['cart_token'=>$token,'variant_id'=>$variant->id]);
        $quantity=($item->exists?$item->quantity:0)+$data['quantity'];
        if ($quantity>$variant->available()) throw ValidationException::withMessages(['quantity'=>'Insufficient stock.']);
        $item->quantity=$quantity;$item->save(); return redirect()->route('cart')->with('message','Added to your bag.');
    }
    public function update(Request $request,Cart $cart,CartItem $item) {
        abort_unless($item->cart_token===$cart->token($request),404);
        $quantity=$request->validate(['quantity'=>'required|integer|min:1|max:20'])['quantity'];
        if ($quantity>$item->variant->available()) throw ValidationException::withMessages(['quantity'=>'Insufficient stock.']);
        $item->update(['quantity'=>$quantity]);return back();
    }
    public function delete(Request $request,Cart $cart,CartItem $item) {abort_unless($item->cart_token===$cart->token($request),404);$item->delete();return back();}
}
