<?php
namespace App\Http\Controllers;
use App\Services\{Cart,Checkout,Pricing};
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class CheckoutController extends Controller {
    public function show(Request $request,Cart $cart) {
        $items=$cart->items($request); if ($items->isEmpty()) return redirect()->route('cart');
        if (!$request->session()->has('checkout_token')) $request->session()->put('checkout_token',(string)Str::uuid());
        $address=$request->user()?->addresses()->where('is_default',true)->first();
        return view('store.checkout',compact('items','address'));
    }
    public function place(Request $request,Cart $cart,Checkout $checkout) {
        $data=$request->validate(['checkout_token'=>'required|uuid','email'=>'required|email|max:255','name'=>'required|string|max:100','phone'=>'required|regex:/^[6-9][0-9]{9}$/','line1'=>'required|string|max:200','line2'=>'nullable|string|max:200','city'=>'required|string|max:100','state'=>'required|string|max:100','pincode'=>'required|regex:/^[1-9][0-9]{5}$/','coupon'=>'nullable|string|max:40','payment_method'=>'required|in:razorpay,cod']);
        abort_unless(hash_equals($request->session()->get('checkout_token',''),$data['checkout_token']),419);
        $address=collect($data)->only(['name','phone','line1','line2','city','state','pincode'])->all();
        $order=$checkout->place($cart->token($request),$request->user()?->id,$data['checkout_token'],$data['email'],$address,$data['coupon']??null,$data['payment_method']);
        $request->session()->put('order_access_'.$order->id,true);
        $request->session()->forget('checkout_token');
        return redirect()->route($order->payment_method==='cod'?'order.status':'payment.show',$order);
    }
}
