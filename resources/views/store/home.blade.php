@extends('layouts.app')
@section('main_class','home-content')
@section('content')
<section class="editorial-hero" aria-labelledby="hero-title">
    <div class="hero-copy">
        <p class="eyebrow hero-eyebrow">The everyday edit · Earrings & bangles</p>
        <h1 id="hero-title">A little light<br>for <em>every day.</em></h1>
        <p>Easy pieces, thoughtful details. Find the earrings and bangles you will reach for again and again.</p>
        <div class="hero-actions">
            <a class="button button-cream" href="{{route('shop')}}">Shop the collection <span aria-hidden="true">↗</span></a>
            <a class="hero-text-link" href="{{route('page','about')}}">Our story <span aria-hidden="true">↗</span></a>
        </div>
        <span class="hero-index">01 / The collection</span>
    </div>
    <div class="hero-visual">
        <img src="{{asset('images/hero.webp')}}" alt="Gold bangles and pearl drop earrings on burgundy velvet" width="1000" height="666" fetchpriority="high">
    </div>
</section>

<div class="benefit-strip" aria-label="Shopping information">
    <span><span class="benefit-icon" aria-hidden="true">✧</span> Curated earrings & bangles</span>
    <span><span class="benefit-icon" aria-hidden="true">◇</span> Find your bangle size</span>
    <span><span class="benefit-icon" aria-hidden="true">↗</span> Delivery details at checkout</span>
</div>

<section class="home-section" aria-labelledby="category-title">
    <div class="section-heading">
        <div><p class="eyebrow">Explore by style</p><h2 id="category-title">The pieces to know</h2></div>
        <p>From subtle finishing touches to the centre of your look.</p>
    </div>
    <div class="category-grid">
        @foreach($categories as $category)
        @php($categoryImage=$featured->firstWhere('category_id',$category->id)?->images->first())
        <a href="{{route('category',$category)}}" class="category-tile">
            <div class="category-image">
                <img src="{{$categoryImage?->url() ?? asset($category->slug==='earrings'?'images/demo/arc-mini-hoops.webp':'images/demo/nira-gold-bangles.webp')}}" alt="{{$category->name}} collection" loading="lazy" width="960" height="960">
            </div>
            <div class="category-caption"><span><small>Discover the collection</small><strong>{{$category->name}}</strong></span><span class="circle-arrow" aria-hidden="true">↗</span></div>
        </a>
        @endforeach
    </div>
</section>

<section class="home-section" aria-labelledby="featured-title">
    <div class="section-heading section-heading-line">
        <div><p class="eyebrow">The jewellery box</p><h2 id="featured-title">A few favourites</h2></div>
        <a class="text-link" href="{{route('shop')}}">View all pieces <span aria-hidden="true">↗</span></a>
    </div>
    <div class="grid-products">
        @foreach($featured as $item)@include('store._card')@endforeach
    </div>
</section>

<section class="story-panel">
    <div>
        <p class="eyebrow">Find your fit</p>
        <h2>Every detail<br><em>matters.</em></h2>
        <p>Make a bangle your own. Check the size guide, choose a finish and see every detail before you add it to your bag.</p>
        <a class="button button-light" href="{{route('size-guide')}}">Explore the size guide <span aria-hidden="true">↗</span></a>
    </div>
    <img src="{{asset('images/demo/tara-bangle-set.webp')}}" alt="Illustrative rose gold bangle set" loading="lazy" width="960" height="960">
</section>
@endsection
