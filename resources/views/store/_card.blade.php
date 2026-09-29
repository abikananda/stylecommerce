<article class="product-card">
    <a href="{{route('product',$item)}}" aria-label="View {{$item->name}}">
        <div class="product-card-media">
            @if($item->images->first())
                <img loading="lazy" src="{{$item->images->first()->url()}}" alt="{{$item->images->first()->alt}}" @if($item->demoImageUrl()) onerror="this.onerror=null;this.src='{{$item->demoImageUrl()}}'" @endif>
            @elseif($item->demoImageUrl())
                <img loading="lazy" src="{{$item->demoImageUrl()}}" alt="Illustrative photo of {{$item->name}}">
            @else
                <span class="product-image-fallback serif">{{$item->category?->name ?? 'Jewellery'}}</span>
            @endif
            @if($item->variants->every(fn($variant)=>$variant->available()===0))<span class="product-badge">Sold out</span>@endif
        </div>
        <div class="product-card-info">
            <span class="product-category">{{$item->category?->name}}</span>
            <h3>{{$item->name}}</h3>
            <div class="product-card-bottom"><span class="price">From ₹{{number_format(($item->variants->min('price_paise')??0)/100,2)}}</span><span class="product-arrow" aria-hidden="true">↗</span></div>
        </div>
    </a>
</article>
