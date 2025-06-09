@switch($ingredient->ingredient_type)
    @case('Item')
        <div class="row no-gutters align-items-center">
            <div class="col-auto pr-2 font-weight-bold">
                x{{ $ingredient->quantity }}
            </div>
            @if (isset($ingredient->ingredient->image_url))
                <div class="col-auto">
                    <div class="recipe-icon-container">
                        <img class="img-fluid mh-100" src="{{ $ingredient->ingredient->image_url }}" alt="{{ $ingredient->ingredient->name }}">
                    </div>
                </div>
            @endif
            <div class="col pl-2 d-flex justify-content-between">
                <div>
                    {!! $ingredient->ingredient->displayName !!}
                </div>
                @if (Auth::check())
                    @if ($ingredient->hasIngredient(Auth::user()))
                        <span class="badge btn-sm btn-success ml-1 p-2" data-toggle="tooltip" title="You have this ingredient!">
                            <i class="fas fa-check" style="margin-right: 1px;"></i>
                        </span>
                    @else
                        <span class="badge btn-sm btn-danger ml-1 p-2" data-toggle="tooltip" title="You're missing this ingredient...">
                            <i class="fas fa-times" style="margin-right: 1px;"></i>
                        </span>
                    @endif
                @endif
            </div>
        </div>
    @break

    @case('MultiItem')
        <div class="font-weight-bold">
            Any mix of <b>x{{ $ingredient->quantity }}</b> item{{ $ingredient->quantity == 1 ? '' : 's' }} from the following:
        </div>
        <p class="mb-0">
            @foreach ($ingredient->ingredient as $key => $ing)
                @if (isset($ing->image_url))
                    <img class="img-fluid" src="{{ $ing->image_url }}" alt="{{ $ing->name }}">
                @endif
                <strong>{!! $ing->displayName !!}</strong>
                {{ $key < $ingredient->ingredient->count() - 1 && $ingredient->ingredient->count() > 2 ? ', ' : '' }}{{ $key == $ingredient->ingredient->count() - 2 && $ingredient->ingredient->count() > 1 ? ' or ' : '' }}
            @endforeach
        </p>
    @break

    @case('Category')
        <b>x{{ $ingredient->quantity }}</b> item{{ $ingredient->quantity == 1 ? '' : 's' }} from the
        @if (isset($ingredient->ingredient->image_url))
            <img class="img-fluid" src="{{ $ingredient->ingredient->image_url }}">
        @endif
        {!! $ingredient->ingredient->displayName !!} category
    @break

    @case('MultiCategory')
        <!-- This doesn't work yet! -->
        <div class="font-weight-bold">
            Any mix of <b>x{{ $ingredient->quantity }}</b> item{{ $ingredient->quantity == 1 ? '' : 's' }} from the following categories:
        </div>
        @foreach ($ingredient->ingredient as $ing)
            <div>
                -
                @if (isset($ing->image_url))
                    <img class="img-fluid" src="{{ $ing->image_url }}" alt="{{ $ing->name }}">
                @endif
                <span>{!! $ing->displayName !!}</span>
            </div>
        @endforeach
    @break

    @case('Currency')
        <div class="row no-gutters align-items-center">
            <div class="col-auto pr-2 font-weight-bold">
                x{{ $ingredient->quantity }}
            </div>
            @if (isset($ingredient->ingredient->currencyImageUrl) || isset($ingredient->ingredient->currencyIconUrl))
                <div class="col-auto">
                    <div class="recipe-icon-container">
                        <img class="img-fluid mh-100" src="{{ $ingredient->ingredient->currencyImageUrl ?? $ingredient->ingredient->currencyIconUrl }}" alt="{{ $ingredient->ingredient->name }}">
                    </div>
                </div>
            @endif
            <div class="col pl-2">
                {!! $ingredient->ingredient->display_name !!}
                @if (Auth::check())
                    @if ($ingredient->hasIngredient(Auth::user()))
                        <span class="badge btn-sm btn-success ml-1 p-1" data-toggle="tooltip" title="You have this ingredient!" style="font-size: 90%;">
                            <i class="fas fa-check" style="margin-right: 1px;"></i>
                        </span>
                    @else
                        <span class="badge btn-sm btn-danger ml-1" data-toggle="tooltip" title="You're missing this ingredient..." style="font-size: 90%;">
                            <i class="fas fa-times" style="margin-right: 1px;"></i>
                        </span>
                    @endif
                @endif
            </div>
        </div>
    @break

@endswitch
