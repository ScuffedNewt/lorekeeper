@extends('shops.layout')

@section('shops-title') Shop Index @endsection

@section('shops-content')
{!! breadcrumbs(['Shops' => 'shops']) !!}

<h1>
    Shops
</h1>

<div class="row shops-row">
    @foreach($shops as $shop)
    @if($shop->is_staff)
        @if(auth::check() && auth::user()->isstaff)
            <div class="col-md-3 col-6 mb-3 text-center">
                <div class="shop-image">
                    <a href="{{ $shop->url }}"><img src="{{ $shop->shopImageUrl }}" /></a>
                </div>
                <div class="shop-name mt-1">
                    <a href="{{ $shop->url }}" class="h5 mb-0"><i class="fas fa-crown mr-1"></i>{{ $shop->name }}</a>
                    <br>
                    @if($shop->is_restricted)
                        <div class="text-muted small">(Requires <?php 
                            $limits = []; 
                            foreach($shop->limits as $limit)
                            {
                            $name = $limit->item->name;
                            $limits[] = $name;
                            }
                            echo implode(", ", $limits);
                        ?>)</div>
                    @endif
                    @if($shop->is_fto)
                        <span class="badge badge-pill badge-success">FTO Shop</span>
                    @endif
                </div>
            </div>
        @endif
    @else
        <div class="col-md-3 col-6 mb-3 text-center">
            <div class="shop-image">
                <a href="{{ $shop->url }}"><img src="{{ $shop->shopImageUrl }}" alt="{{ $shop->name }}" /></a>
            </div>
            <div class="shop-name mt-1">
                <a href="{{ $shop->url }}" class="h5 mb-0">{{ $shop->name }}</a>
                <br>
                @if($shop->is_restricted)
                    <div class="text-muted small">(Requires <?php 
                        $limits = []; 
                        foreach($shop->limits as $limit)
                        {
                        $name = $limit->item->name;
                        $limits[] = $name;
                        }
                        echo implode(", ", $limits);
                    ?>)</div>
                @endif
                @if($shop->is_fto)
                    <span class="badge badge-pill badge-success">FTO Shop</span>
                @endif
            </div>
        </div>
        @endif
    @endforeach
</div>

@endsection
