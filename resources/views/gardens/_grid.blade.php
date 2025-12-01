<div id="gridView" class="{{ Settings::get('garden_view') === 0 ? (config('lorekeeper.garden.garden_view') == 'grid' ? '' : 'hide') : '' }}">
    @foreach ($groups as $group => $gardens)
        <div class="card text-center h-100">
            <div class="card-header">
                <h3>{{ $group }}</h3>
            </div>
            <div class="card-body">
                <div class="row justify-content-center">
                    @foreach ($gardens as $garden)
                        <div class="col-md-6 mb-3">
                            @if ($garden->has_image)
                                <a href="{{ $garden->imageUrl }}" data-lightbox="entry" data-title="{{ $garden->name }}">
                                    <img src="{{ $garden->imageUrl }}" class="img-fluid">
                                </a>
                            @else
                                <div class="alert alert-info">No image set.</div>
                            @endif
                            <h2>{{ $garden->name }}</h2>
                            <a href="{{ url('gardens/' . str_replace(' ', '-', strtolower($garden->name))) }}" class="btn btn-primary">Enter {{ $garden->name }}</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>