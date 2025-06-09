<div class="row no-gutters align-items-center">
    <div class="col-auto pr-2 font-weight-bold">
        x{{ $reward['quantity'] }}
    </div>
    @if (isset($reward['asset']->imageUrl) || isset($reward['asset']->currencyImageUrl) || isset($reward['asset']->currencyIconUrl))
        <div class="col-auto">
            <div class="recipe-icon-container">
                <img class="img-fluid mh-100" src="{{ $reward['asset']->imageUrl ?? ($reward['asset']->currencyImageUrl ?? $reward['asset']->currencyIconUrl) }}">
            </div>
        </div>
    @endif
    <div class="col pl-2">
        {!! $reward['asset']->displayName !!}
    </div>
</div>
