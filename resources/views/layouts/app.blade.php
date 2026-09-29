<!doctype html>
<html lang="en-IN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#321f2a">
    <title>@yield('title', $brand.' — Earrings & Bangles')</title>
    <meta name="description" content="@yield('description','Discover earrings and bangles made for everyday and special occasions.')">
    <link rel="canonical" href="{{ url()->current() }}">
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body style="--brand-accent:{{ $brandColour }}">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="announcement"><span>Small details. Lasting impressions.</span><span class="announcement-divider" aria-hidden="true">✦</span><span>Delivery across India</span></div>
    <header class="site-header">
        <div class="shell header-inner">
            <details class="mobile-menu">
                <summary aria-label="Open menu"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h18M3 12h18M3 17h18"/></svg></summary>
                <nav class="mobile-menu-panel" aria-label="Mobile navigation">
                    <a href="{{route('shop')}}">Shop all</a>
                    <a href="{{route('category','earrings')}}">Earrings</a>
                    <a href="{{route('category','bangles')}}">Bangles</a>
                    <a href="{{route('size-guide')}}">Bangle size guide</a>
                    <a href="{{route('page','about')}}">Our story</a>
                    <a href="{{route('wishlist')}}">Wishlist</a>
                    <a href="{{auth()->check()?route('account'):route('login')}}">Account</a>
                </nav>
            </details>
            <nav class="primary-nav" aria-label="Main navigation">
                <a href="{{route('shop')}}">Shop all</a>
                <a href="{{route('category','earrings')}}">Earrings</a>
                <a href="{{route('category','bangles')}}">Bangles</a>
            </nav>
            <a href="{{route('home')}}" class="wordmark" aria-label="{{$brand}} home">
                @if($brandLogo)<img src="{{asset('storage/'.$brandLogo)}}" alt="{{$brand}}" class="brand-logo">
                @else{{ $brand }}@endif
            </a>
            <div class="header-actions">
                <form class="header-search" action="{{route('shop')}}" method="get" role="search">
                    <label class="sr-only" for="header-search">Search jewellery</label>
                    <input id="header-search" name="q" type="search" placeholder="Search" value="{{request('q')}}" autocomplete="off">
                    <button aria-label="Search" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.4"/><path d="m16 16 5 5"/></svg></button>
                </form>
                <a class="icon-link optional-action" href="{{route('wishlist')}}" aria-label="Wishlist"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.2c0 4.5-8.8 10.6-8.8 10.6S3.2 12.7 3.2 8.2a4.3 4.3 0 0 1 8.8-1.1 4.3 4.3 0 0 1 8.8 1.1Z"/></svg></a>
                <a class="icon-link optional-action" href="{{auth()->check()?route('account'):route('login')}}" aria-label="Account"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c.5-3.6 3.2-5.5 7.5-5.5s7 1.9 7.5 5.5"/></svg></a>
                <a class="bag-link" href="{{route('cart')}}" aria-label="Shopping bag"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h16l-1.2 12H5.2L4 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg><span>Bag</span></a>
            </div>
        </div>
        <form class="mobile-search shell" action="{{route('shop')}}" method="get" role="search">
            <label class="sr-only" for="mobile-search">Search jewellery</label>
            <input id="mobile-search" name="q" type="search" placeholder="Search earrings, bangles and more" value="{{request('q')}}">
            <button type="submit" aria-label="Search">Search</button>
        </form>
    </header>
    <main id="main-content" class="shell @yield('main_class','page-content')">
        @if(session('message'))<div class="notice" role="status">{{session('message')}}</div>@endif
        @if($errors->any())<div class="error" role="alert"><strong>Please check the following:</strong><ul class="list-disc pl-5 mt-2">@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
    <footer class="site-footer">
        <div class="shell footer-top">
            <div>
                <a href="{{route('home')}}" class="footer-brand">{{$brand}}</a>
                <p>Thoughtful jewellery for everyday moments and everything in between.</p>
                @if(\App\Models\Setting::valueOf('instagram_url'))<a class="footer-social" href="{{\App\Models\Setting::valueOf('instagram_url')}}" rel="noopener noreferrer">Find us on Instagram ↗</a>@endif
            </div>
            <nav aria-label="Shop links"><h2>Explore</h2><a href="{{route('shop')}}">Shop all</a><a href="{{route('category','earrings')}}">Earrings</a><a href="{{route('category','bangles')}}">Bangles</a><a href="{{route('size-guide')}}">Bangle size guide</a></nav>
            <nav aria-label="Customer information"><h2>Help</h2><a href="{{route('page','contact')}}">Contact us</a><a href="{{route('page','shipping')}}">Shipping</a><a href="{{route('page','returns')}}">Returns</a><a href="{{route('page','about')}}">Our story</a></nav>
            <nav aria-label="Policies"><h2>Information</h2><a href="{{route('page','privacy')}}">Privacy</a><a href="{{route('page','terms')}}">Terms</a>@if(\App\Models\Setting::valueOf('brand_email'))<a href="mailto:{{\App\Models\Setting::valueOf('brand_email')}}">{{\App\Models\Setting::valueOf('brand_email')}}</a>@endif</nav>
        </div>
        <div class="shell footer-bottom"><span>© {{date('Y')}} {{$brand}}</span><span>Made to be worn your way.</span></div>
    </footer>
    @livewireScripts
    @stack('scripts')
</body>
</html>
