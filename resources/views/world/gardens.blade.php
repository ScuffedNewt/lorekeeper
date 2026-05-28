@extends('world.layout')

@section('world-title')
    Gardens
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Gardens' => 'world/gardens']) !!}
    <h1>Gardens</h1>

    {!! Form::open(['method' => 'GET']) !!}
    <div class="form-inline justify-content-end mb-3">
        {!! Form::text('name', Request::get('name'), ['class' => 'form-control mr-2', 'placeholder' => 'Name']) !!}
        {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}

    {!! $gardens->render() !!}
    @foreach ($gardens as $garden)
        <div class="card mb-3">
            <div class="card-body">
                <x-admin-edit title="Garden" :object="$garden" />
                <div class="row">
                    @if ($garden->imageUrl)
                        <div class="col-md-3 text-center">
                            <img src="{{ $garden->imageUrl }}" class="img-fluid" alt="{{ $garden->name }}">
                        </div>
                    @endif
                    <div class="{{ $garden->imageUrl ? 'col-md-9' : 'col-12' }}">
                        <h2 class="h3">{{ $garden->name }}</h2>
                        @if ($garden->backgroundImageUrl)
                            <div class="mb-2">
                                <img src="{{ $garden->backgroundImageUrl }}" class="img-fluid rounded" alt="{{ $garden->name }} Background">
                            </div>
                        @endif
                        <div>{!! $garden->description !!}</div>
                        <p><strong>Plots:</strong> {{ $garden->plots->count() }} · <strong>Perishing:</strong> {{ $garden->can_perish ? 'Enabled' : 'Disabled' }}</p>
                        <p><strong>Allowed objects:</strong> {{ collect($garden->allowed_object_types ?: array_keys(config('lorekeeper.garden.garden_object_types')))->map(fn ($type) => config('lorekeeper.garden.garden_object_types.' . $type . '.displayName', ucfirst($type)))->implode(', ') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    {!! $gardens->render() !!}

    <h2 class="h3 mt-4">Garden Objects</h2>
    @foreach ($objects as $type => $gardenObjects)
        <h3 class="h5">{{ config('lorekeeper.garden.garden_object_types.' . $type . '.displayName', ucfirst($type)) }}</h3>
        <div class="row">
            @foreach ($gardenObjects as $gardenObject)
                <div class="col-md-4 mb-3">
                    <div class="card h-100"><div class="card-body text-center">
                        @if ($gardenObject->object?->imageUrl)
                            <img src="{{ $gardenObject->object->imageUrl }}" style="max-height: 100px" alt="{{ $gardenObject->object->name }}">
                        @endif
                        <h4 class="h5 mt-2">{!! $gardenObject->object?->displayName ?? 'Missing Asset' !!}</h4>
                        <small class="d-block"><strong>Season:</strong> {{ $gardenObject->seasonLabel }}</small>
                        <small>{{ $gardenObject->data['interactions'] ?? 0 }} interactions · {{ $gardenObject->data['growth_time'] ?? 0 }} hours each</small>
                    </div></div>
                </div>
            @endforeach
        </div>
    @endforeach
@endsection
