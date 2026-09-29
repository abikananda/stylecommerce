@extends('layouts.app')
@section('title','Checkout · '.$brand)
@section('content')
<nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{route('cart')}}">Your bag</a><span aria-hidden="true">/</span><span>Checkout</span></nav>
<div class="page-heading"><p class="eyebrow">Almost yours</p><h1>Checkout</h1></div>
<div class="checkout-layout">
    <form method="post" action="{{route('checkout.place')}}" class="checkout-form">
        @csrf
        <input type="hidden" name="checkout_token" value="{{session('checkout_token')}}">
        <section class="checkout-section">
            <div class="checkout-section-heading"><span>01</span><div><h2>Contact details</h2><p>We will send order updates to this email.</p></div></div>
            <div class="form-grid">
                <div><label class="label" for="email">Email address</label><input class="field" id="email" name="email" type="email" autocomplete="email" value="{{old('email',auth()->user()?->email)}}" required></div>
                <div><label class="label" for="phone">Mobile number</label><input class="field" id="phone" name="phone" value="{{old('phone',$address?->phone)}}" inputmode="numeric" autocomplete="tel" required></div>
            </div>
        </section>
        <section class="checkout-section">
            <div class="checkout-section-heading"><span>02</span><div><h2>Delivery address</h2><p>Enter the address where you would like your order delivered.</p></div></div>
            <div class="form-grid">
                <div class="full"><label class="label" for="name">Full name</label><input class="field" id="name" name="name" value="{{old('name',$address?->name)}}" autocomplete="name" required></div>
                <div class="full"><label class="label" for="line1">Address line 1</label><input class="field" id="line1" name="line1" value="{{old('line1',$address?->line1)}}" autocomplete="address-line1" required></div>
                <div class="full"><label class="label" for="line2">Address line 2 (optional)</label><input class="field" id="line2" name="line2" value="{{old('line2',$address?->line2)}}" autocomplete="address-line2"></div>
                <div><label class="label" for="city">City</label><input class="field" id="city" name="city" value="{{old('city',$address?->city)}}" autocomplete="address-level2" required></div>
                <div><label class="label" for="state">State</label><input class="field" id="state" name="state" value="{{old('state',$address?->state)}}" autocomplete="address-level1" required></div>
                <div><label class="label" for="pincode">PIN code</label><input class="field" id="pincode" name="pincode" value="{{old('pincode',$address?->pincode)}}" inputmode="numeric" autocomplete="postal-code" maxlength="6" required></div>
            </div>
        </section>
        <section class="checkout-section">
            <div class="checkout-section-heading"><span>03</span><div><h2>Payment</h2><p>Review the verified total before opening secure payment.</p></div></div>
            <label class="label" for="coupon">Coupon code (optional)</label>
            <input class="field coupon-field" id="coupon" name="coupon" value="{{old('coupon')}}" autocomplete="off">
            <fieldset class="payment-options">
                <legend class="sr-only">Payment method</legend>
                <label><input type="radio" name="payment_method" value="razorpay" @checked(old('payment_method','razorpay')==='razorpay')> <span>Pay online with Razorpay<small>Secure payment on the next step</small></span></label>
                @if(\App\Models\Setting::valueOf('cod_enabled','0')==='1')<label><input type="radio" name="payment_method" value="cod" @checked(old('payment_method')==='cod')> <span>Cash on delivery<small>Pay when your order arrives</small></span></label>@endif
            </fieldset>
        </section>
        <button class="button checkout-submit" type="submit">Review total and continue <span aria-hidden="true">↗</span></button>
    </form>
    <aside class="order-summary checkout-summary">
        <p class="eyebrow">Your selection</p><h2>Order summary</h2>
        @foreach($items as $item)
        <div class="checkout-item"><div><strong>{{$item->variant->product->name}}</strong><span>{{$item->variant->sku}} · Qty {{$item->quantity}}</span></div><span class="price">₹{{number_format($item->variant->price_paise*$item->quantity/100,2)}}</span></div>
        @endforeach
        <div class="summary-row"><span>Subtotal</span><strong class="price">₹{{number_format($items->sum(fn($i)=>$i->variant->price_paise*$i->quantity)/100,2)}}</strong></div>
        <p class="summary-note">Shipping, coupons and applicable tax are confirmed for your PIN code on the next screen.</p>
    </aside>
</div>
@endsection
