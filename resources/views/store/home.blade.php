@extends('layouts.app') @section('content')
<section class="grid md:grid-cols-2 gap-8 lg:gap-14 items-stretch py-3 md:py-8">
  <div class="flex flex-col justify-center py-8 md:py-14 hero-copy">
    <p class="uppercase tracking-[.24em] text-[11px] font-semibold gold mb-5">A considered jewellery collection</p>
    <h1 class="section-title">A little light,<br><em>every day.</em></h1>
    <p class="muted mt-6 max-w-lg text-[15px] md:text-base leading-7">Discover earrings and bangles designed to slip effortlessly into your everyday — with thoughtful details for the moments worth dressing up for.</p>
    <div class="flex flex-wrap gap-3 mt-9"><a class="button" href="{{route('shop')}}">Explore collection <span>→</span></a><a class="button button-light" href="{{route('category','earrings')}}">Shop earrings</a></div>
    <div class="flex gap-7 mt-10 pt-7 border-t border-stone-200 text-xs text-stone-500"><span>Curated designs</span><span>Ships across India</span><span>Easy checkout</span></div>
  </div>
  <div class="hero-panel min-h-[430px] md:min-h-[590px]">
    <img src="{{asset('images/hero.webp')}}" alt="Illustrative gold bangles and pearl drop earrings on burgundy velvet" width="1400" height="933" fetchpriority="high">
    <div class="hero-badge">New season · Everyday shine</div>
  </div>
</section>
<section class="py-16 md:py-24">
  <div class="flex justify-between items-end mb-8"><div><p class="gold uppercase tracking-[.2em] text-[10px] font-semibold">Handpicked for you</p><h2 class="serif text-4xl md:text-5xl mt-2">The edit</h2></div><a class="text-sm underline underline-offset-4" href="{{route('shop')}}">View all pieces →</a></div>
  <div class="grid-products">@foreach($featured as $item)@include('store._card')@endforeach</div>
</section>
<section class="py-8 md:py-14">
  <div class="mb-8"><p class="gold uppercase tracking-[.2em] text-[10px] font-semibold">Find your style</p><h2 class="serif text-4xl md:text-5xl mt-2">Shop by category</h2></div>
  <div class="grid gap-4 md:grid-cols-2">@foreach($categories as $category)<a href="{{route('category',$category)}}" class="card category-card card-hover"><div class="gold text-[10px] uppercase tracking-[.18em] font-semibold">Discover</div><h2 class="serif text-4xl mt-3">{{$category->name}}</h2><span class="text-sm mt-5 category-arrow">Shop now&nbsp; →</span></a>@endforeach</div>
</section>
<section class="py-16 md:py-24 text-center"><p class="gold uppercase tracking-[.2em] text-[10px] font-semibold">Made for your moments</p><h2 class="serif text-4xl md:text-5xl mt-3">Jewellery that feels like you.</h2><p class="muted max-w-xl mx-auto mt-5 leading-7">Keep it simple. Layer it up. Gift it. Style every piece your own way.</p><a class="button mt-7" href="{{route('shop')}}">Discover the collection</a></section>
@endsection