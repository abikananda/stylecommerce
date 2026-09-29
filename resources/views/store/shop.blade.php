@extends('layouts.app')
@section('title','Shop earrings & bangles · '.$brand)
@section('content')
<nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{route('home')}}">Home</a><span aria-hidden="true">/</span><span>Shop</span></nav>
<div class="catalogue-intro">
    <p class="eyebrow">The collection</p>
    <h1>{{ $categories->firstWhere('slug',request('category'))?->name ?? 'Find your next favourite' }}</h1>
    <p>Explore the pieces that make an everyday moment feel a little more special.</p>
</div>
<div class="catalogue-layout">
    <aside class="catalogue-sidebar">
        <details class="filter-panel" open>
            <summary>Filter & sort <span aria-hidden="true">⌄</span></summary>
            <form action="{{route('shop')}}" method="get" class="filter-form">
                <label class="label" for="catalogue-search">Search</label>
                <input class="field" id="catalogue-search" type="search" name="q" value="{{request('q')}}" placeholder="Search pieces">
                <label class="label" for="filter-category">Category</label>
                <select class="field" id="filter-category" name="category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{$category->slug}}" @selected(request('category')===$category->slug)>{{$category->name}}</option>@endforeach</select>
                @foreach(['material','colour','style','occasion'] as $field)
                <label class="label" for="filter-{{$field}}">{{ucfirst($field)}}</label>
                <select class="field" id="filter-{{$field}}" name="{{$field}}"><option value="">All {{$field}}s</option>@foreach($filters->pluck($field)->filter()->unique()->sort() as $value)<option value="{{$value}}" @selected(request($field)===$value)>{{$value}}</option>@endforeach</select>
                @endforeach
                <div class="filter-price">
                    <div><label class="label" for="filter-min">Min price</label><input class="field" id="filter-min" name="min" type="number" min="0" step="1" value="{{request('min')}}" placeholder="₹"></div>
                    <div><label class="label" for="filter-max">Max price</label><input class="field" id="filter-max" name="max" type="number" min="0" step="1" value="{{request('max')}}" placeholder="₹"></div>
                </div>
                <label class="label" for="filter-sort">Sort by</label>
                <select class="field" id="filter-sort" name="sort"><option value="latest">Newest</option><option value="price_asc" @selected(request('sort')==='price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort')==='price_desc')>Price: high to low</option></select>
                <label class="filter-check"><input type="checkbox" name="available" value="1" @checked(request('available'))> In stock only</label>
                <button class="button" type="submit">Apply filters</button>
                <a class="clear-filters" href="{{route('shop')}}">Clear all</a>
            </form>
        </details>
    </aside>
    <div class="catalogue-results">
        <div class="results-bar"><span>{{$products->total()}} {{\\Illuminate\\Support\\Str::plural('piece',$products->total())}}</span><span>Thoughtfully chosen for you</span></div>
        @if($products->count())
        <div class="grid-products">@foreach($products as $item)@include('store._card')@endforeach</div>
        <div class="pagination-wrap">{{$products->links()}}</div>
        @else
        <div class="empty-state"><span aria-hidden="true">◇</span><h2>No pieces found</h2><p>Try another search or remove a filter to see more jewellery.</p><a class="button button-light" href="{{route('shop')}}">View all pieces</a></div>
        @endif
    </div>
</div>
@endsection
