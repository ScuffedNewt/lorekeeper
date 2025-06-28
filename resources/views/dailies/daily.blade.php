@extends('dailies.layout')

@section('dailies-title')
    {{ $daily->name }}
@endsection

@section('dailies-content')

    <head>
        <!-- Scripts for wheel of fortune dailies -->
        <script src="{{ asset('js/winwheel.min.js') }}"></script>
        <script src="{{ asset('js/tweenmax.min.js') }}"></script>
    </head>
    {!! breadcrumbs([ucfirst(__('dailies.dailies')) => __('dailies.dailies'), $daily->name => $daily->url]) !!}

    <h1>
        {{ $daily->name }}
    </h1>
    @php
        $isDisabled = isset($cooldown) || !Auth::check() || ($daily->currency_id != null && Auth::user()->getCurrencies(true)->where('id', $daily->currency_id)->first()->quantity < $daily->fee);
    @endphp

    @if (count(getLimits($daily)))
        <h4>
            <span class="badge badge-warning">
                <i class="fas fa-exclamation-triangle"></i>
                This {{ __('dailies.daily') }} takes a fee to play!
            </span>
            @include('widgets._limits', [
                'object' => $daily,
                'hideUnlock' => true,
            ])
        </h4>
    @endif

    @include('dailies.types._' . strtolower($daily->type) . '_daily', ['wheel' => $daily->wheel])
@endsection
