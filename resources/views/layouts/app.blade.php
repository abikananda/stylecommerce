<!doctype html><html lang="en-IN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#241d1b"><title>@yield('title', $brand.' — Earrings & Bangles')</title><meta name="description" content="@yield('description','Discover earrings and bangles made for everyday and special occasions.')"><link rel="canonical" href="{{ url()->current() }}">@vite(['resources/css/app.css','resources/js/app.js'])@livewireStyles @stack('head')</head>
<body style="--brand-accent:{{ $brandColour }}">
<div class="banner">Thoughtful details, made to be worn often · Ships across India</div>
<header class="store-header">
  <div class="shell header-main flex items-center justify-between gap-5">
    <a href="{{ route('home') }}" class="brand-mark serif text-2xl md:text-3xl ink shrink-0">
      @if($brandLogo)<img src="{{asset('storage/'.$brandLogo)}}" alt="{{$brand}}" class="max-h-12 max-w-40 object-contain">@else{{ $brand }}@endif
    </a>
    <nav class="desktop-nav flex items-center gap-8" aria-label="Main navigation">
      <a class="navlink" href="{{ route('shop') }}">Shop all</a>
      <a class="navlink" href="{{ route('category','earrings') }}">Earrings</a>
      <a class="navlink" href="{{ route('category','bangles') }}">Bangles</a>
      <a class="navlink" href="{{ route('size-guide') }}">Size guide</a>
    </nav>
    <div class="flex gap-2 items-center">
      <a class="header-action" href="{{ route('wishlist') }}" aria-label="Wishlist" title="Wishlist">♡</a>
      <a class="header-action hidden sm:grid" href="{{ auth()->check()?route('account'):route('login') }}" aria-label="Account" title="Account">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 21c.7-4 3.4-6 8-6s7.3 2 8 6"/></svg>
      </a>
      <a class="header-action relative" href="{{ route('cart') }}" aria-label="Shopping bag" title="Shopping bag">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 8h12l1 12H5L6 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg>
      </a>
    </div>
  </div>
  <nav class="mobile-nav hidden justify-around border-t border-stone-200 px-3 py-2 text-xs" aria-label="Mobile navigation">
    <a class="navlink" href="{{ route('shop') }}">Shop</a><a class="navlink" href="{{ route('category','earrings') }}">Earrings</a><a class="navlink" href="{{ route('category','bangles') }}">Bangles</a><a class="navlink" href="{{ route('size-guide') }}">Sizes</a>
  </nav>
</header>
<main class="shell py-7 md:py-12 min-h-[65vh]">
  @if(session('message'))<div class="notice" role="status">{{ session('message') }}</div>@endif
  @if($errors->any())<div class="error" role="alert"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  @yield('content')
</main>
<footer class="bg-[#241d1b] text-white mt-10">
  <div class="shell py-16 grid gap-12 md:grid-cols-3">
    <div><div class="serif text-3xl">{{ $brand }}</div><p class="text-stone-300 mt-4 max-w-sm">Thoughtfully designed earrings and bangles for everyday rituals, celebrations and everything between.</p>
      @if(\App\Models\Setting::valueOf('brand_email'))<a class="footer-link text-sm block mt-5" href="mailto:{{\App\Models\Setting::valueOf('brand_email')}}">{{\App\Models\Setting::valueOf('brand_email')}}</a>@endif
      @if(\App\Models\Setting::valueOf('instagram_url'))<a class="footer-link text-sm underline block mt-3" href="{{\App\Models\Setting::valueOf('instagram_url')}}" rel="noopener noreferrer">Instagram</a>@endif
    </div>
    <div><p class="uppercase tracking-[.18em] text-[10px] text-stone-400 mb-5">Explore</p><div class="grid gap-3 text-sm"><a class="footer-link" href="{{route('shop')}}">Shop</a><a class="footer-link" href="{{route('size-guide')}}">Bangle size guide</a><a class="footer-link" href="{{route('page','about')}}">About us</a><a class="footer-link" href="{{route('page','contact')}}">Contact</a></div></div>
    <div><p class="uppercase tracking-[.18em] text-[10px] text-stone-400 mb-5">Information</p><div class="grid gap-3 text-sm">@foreach(['shipping','returns','privacy','terms'] as $page)<a class="footer-link" href="{{route('page',$page)}}">{{ucfirst($page)}}</a>@endforeach</div></div>
  </div>
  <div class="border-t border-white/10"><div class="shell py-5 text-xs text-stone-500">© {{date('Y')}} {{ $brand }} · Made to be worn often.</div></div>
</footer>
@livewireScripts @stack('scripts')</body></html>
