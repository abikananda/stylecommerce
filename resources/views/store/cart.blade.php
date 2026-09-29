@extends('layouts.app')
@section('title','Your bag · '.$brand)
@section('content')
<nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{route('home')}}">Home</a><span aria-hidden="true">/</span><span>Your bag</span></nav>
<div class="page-heading"><p class="eyebrow">Your selection</p><h1>Your bag <span>({{$items->sum('quantity')}})</span></h1></div>
@if($items->count())
<div class="cart-layout">
    <div class="cart-items">
        @foreach($items as $item)
        @php($cartProduct=$item->variant->product)
        <article class="cart-item">
            <a class="cart-image" href="{{route('product',$cartProduct)}}">
                @if($cartProduct->images->first())
                    <img src="{{$cartProduct->images->first()->url()}}" alt="{{$cartProduct->images->first()->alt}}" @if($cartProduct->demoImageUrl()) onerror="this.onerror=null;this.src='{{$cartProduct->demoImageUrl()}}'" @endif>
                @elseif($cartProduct->demoImageUrl())
                    <img src="{{$cartProduct->demoImageUrl()}}" alt="Illustrative photo of {{$cartProduct->name}}">
                @endif
            </a>
            <div class="cart-item-body">
                <p class="eyebrow">{{$cartProduct->category?->name}}</p>
                <h2><a href="{{route('product',$cartProduct)}}">{{$cartProduct->name}}</a></h2>
                <p class="muted cart-variant">{{$item->variant->sku}} · {{implode(' / ',array_filter([$item->variant->size,$item->variant->colour,$item->variant->set_size]))}}</p>
                <p class="price cart-price">₹{{number_format($item->variant->price_paise/100,2)}}</p>
                <div class="cart-controls">
                    <form method="post" action="{{route('cart.update',$item)}}" class="quantity-form">@csrf @method('PATCH')<label for="qty_{{$item->id}}">Qty</label><input class="field" type="number" min="1" max="20" id="qty_{{$item->id}}" name="quantity" value="{{$item->quantity}}"><button type="submit">Update</button></form>
                    <form method="post" action="{{route('cart.delete',$item)}}">@csrf @method('DELETE')<button class="remove-link" type="submit">Remove</button></form>
                </div>
            </div>
        </article>
        @endforeach
        <a href="{{route('shop')}}" class="text-link continue-link">← Continue shopping</a>
    </div>
    <aside class="order-summary">
        <p class="eyebrow">Your order</p><h2>Summary</h2>
        <div class="summary-row"><span>Subtotal</span><strong class="price">₹{{number_format($items->sum(fn($i)=>$i->variant->price_paise*$i->quantity)/100,2)}}</strong></div>
        <p class="summary-note">Shipping, discounts and applicable tax are calculated for your PIN code at checkout.</p>
        <a class="button summary-cta" href="{{route('checkout')}}">Continue to checkout <span aria-hidden="true">↗</span></a>
        <p class="summary-footnote">Your order total will be shown before payment.</p>
    </aside>
</div>
@else
<div class="empty-state"><span aria-hidden="true">◇</span><h2>Your bag is waiting</h2><p>Find a piece that feels like you and add it here.</p><a class="button" href="{{route('shop')}}">Explore jewellery <span aria-hidden="true">↗</span></a></div>
@endif
@endsection
