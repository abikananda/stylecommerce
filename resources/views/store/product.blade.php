@extends('layouts.app')
@section('title',$product->name.' · '.$brand)
@section('description',Str::limit(strip_tags($product->description),155))
@push('head')
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'Product','name'=>$product->name,'description'=>$product->description,'image'=>$product->images->isNotEmpty() ? $product->images->map(fn($image)=>$image->url())->all() : array_values(array_filter([$product->demoImageUrl()])), 'offers'=>['@type'=>'AggregateOffer','priceCurrency'=>'INR','lowPrice'=>number_format(($product->variants->min('price_paise')??0)/100,2,'.',''),'offerCount'=>$product->variants->count()]],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>
@endpush
@section('content')
<nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{route('home')}}">Home</a><span aria-hidden="true">/</span><a href="{{route('category',$product->category)}}">{{$product->category->name}}</a><span aria-hidden="true">/</span><span>{{$product->name}}</span></nav>
<div class="product-layout">
    <div class="product-gallery">
        @forelse($product->images as $image)
        <a class="product-gallery-image" href="{{$image->url()}}" target="_blank" rel="noopener noreferrer" aria-label="Open larger image: {{$image->alt}}">
            <img src="{{$image->url()}}" alt="{{$image->alt}}" @if($product->demoImageUrl()) onerror="this.onerror=null;this.src='{{$product->demoImageUrl()}}'" @endif>
        </a>
        @empty
        <div class="product-gallery-image">@if($product->demoImageUrl())<img src="{{$product->demoImageUrl()}}" alt="Illustrative photo of {{$product->name}}">@else<span class="serif">{{$product->name}}</span>@endif</div>
        @endforelse
    </div>
    <div class="product-details">
        <p class="eyebrow">{{$product->category->name}} / {{$product->style}}</p>
        <h1>{{$product->name}}</h1>
        <p class="product-price price">From ₹{{number_format($product->variants->min('price_paise')/100,2)}}</p>
        <p class="product-description">{{$product->description}}</p>
        <div class="product-meta"><span>Material: {{$product->material ?: 'See selected option'}}</span><span>Finish: {{$product->colour ?: 'See selected option'}}</span></div>
        <form method="post" action="{{route('cart.add')}}" class="product-purchase">
            @csrf
            <label class="label" for="variant_id">Choose your option</label>
            <select class="field" name="variant_id" id="variant_id" required>
                @foreach($product->variants as $variant)
                <option value="{{$variant->id}}" @disabled($variant->available()===0)>{{$variant->sku}} · {{implode(' / ',array_filter([$variant->size,$variant->colour,$variant->material,$variant->set_size]))}} · ₹{{number_format($variant->price_paise/100,2)}} · {{$variant->available()}} available</option>
                @endforeach
            </select>
            @if($product->category->slug==='bangles')<a class="size-link" href="{{route('size-guide')}}">Unsure of your size? See the bangle size guide ↗</a>@endif
            <label class="label" for="quantity">Quantity</label>
            <input class="field quantity-field" id="quantity" name="quantity" type="number" value="1" min="1" max="20">
            <button class="button product-add" type="submit" @disabled($product->variants->every(fn($v)=>$v->available()===0))>{{ $product->variants->every(fn($v)=>$v->available()===0) ? 'Out of stock' : 'Add to bag' }} <span aria-hidden="true">↗</span></button>
        </form>
        @auth
        <form action="{{route('wishlist.toggle',$product)}}" method="post" class="wishlist-form">@csrf<button class="button button-light" type="submit">♡ Save to wishlist</button></form>
        @endauth
        <div class="product-accordions">
            <details open><summary>Details & care <span aria-hidden="true">+</span></summary><p>{{$product->care ?: 'Store separately and wipe gently with a soft cloth.'}}</p></details>
            <details><summary>Delivery & returns <span aria-hidden="true">+</span></summary><p>Delivery availability and charges are confirmed for your PIN code at checkout. <a href="{{route('page','shipping')}}">Shipping information</a> · <a href="{{route('page','returns')}}">Returns information</a></p></details>
        </div>
    </div>
</div>
@if($related->count())
<section class="home-section related-products">
    <div class="section-heading section-heading-line"><div><p class="eyebrow">Keep exploring</p><h2>You may also like</h2></div><a class="text-link" href="{{route('category',$product->category)}}">Shop {{$product->category->name}} ↗</a></div>
    <div class="grid-products">@foreach($related as $item)@include('store._card')@endforeach</div>
</section>
@endif
@endsection
