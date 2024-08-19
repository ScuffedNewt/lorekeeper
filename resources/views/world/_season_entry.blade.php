<div class="row world-entry">
    @if ($season->imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $season->imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $season->imageUrl }}" class="world-entry-image" alt="{{ $season->name }}" /></a></div>
    @endif
    <div class="{{ $season->imageUrl ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Season" :object="$season" />
        <h3>{!! $season->displayName !!}</h3>
        @if ($season->start_month || $season->end_month)
            <div class="text-muted">
                @if ($season->start_month)
                    <div><strong>Starts:</strong> {{ Carbon\Carbon::create(1, $season->start_month)->format('F') }}</div>
                @endif
                @if ($season->end_month)
                    <div><strong>Ends:</strong> {{ Carbon\Carbon::create(1, $season->end_month)->format('F') }}</div>
                @endif
            </div>
        @endif
        @if ($season->summary)
            <div class="text-muted"><i>"{!! $season->summary !!}"</i></div>
        @endif
        {!! $season->parsed_description !!}
        @if ($season->weather->count())
            <hr />
            <h4>Weather</h4>
            <div class="col-12 row">
                @foreach ($season->weather as $weather)
                    <div class="col-md-3 col-6">
                        <div class="card mb-3 text-center">
                            <div class="card-body">
                                @if ($weather->imageUrl)
                                    <a href="{{ $weather->imageUrl }}" data-lightbox="entry" data-title="{{ $weather->name }}"><img src="{{ $weather->imageUrl }}" class="world-entry-image" alt="{{ $weather->name }}" /></a>
                                @endif
                                <h5>{!! $weather->displayName !!}</h5>
                                <div class="text-muted">{!! $weather->summary !!}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>