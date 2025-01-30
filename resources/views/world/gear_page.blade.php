@extends('world.layout')

@section('world-title')
    {{ $gear->name }}
@endsection

@section('meta-img')
    {{ $imageUrl }}
@endsection

@section('meta-desc')
    @if (isset($gear->category) && $gear->category)
        <p><strong>Category:</strong> {{ $gear->category->name }}</p>
    @endif
    :: {!! substr(str_replace('"', '&#39;', $gear->description), 0, 69) !!}
@endsection

@section('content')
    <x-admin-edit title="Gear" :object="$gear" />
    {!! breadcrumbs(['World' => 'world', 'Gears' => 'world/gears', $gear->name => $gear->idUrl]) !!}

    <div class="row">
        <div class="col-sm">
        </div>
        <div class="col-lg-6 col-lg-10">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row world-entry">
                        @if ($imageUrl)
                            <div class="col-md-3 world-entry-image">
                                <a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}">
                                    <img src="{{ $imageUrl }}" class="world-entry-image" alt="{{ $name }}" />
                                </a>
                            </div>
                        @endif
                        <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
                            <h1>
                                @if (!$gear->is_visible)
                                    <i class="fas fa-eye-slash mr-1"></i>
                                @endif
                                {!! $name !!}
                            </h1>
                            <div class="row">
                                @if (isset($gear->category) && $gear->category)
                                    <div class="col-md">
                                        <p>
                                            <strong>Category:</strong>
                                            @if (!$gear->category->is_visible)
                                                <i class="fas fa-eye-slash mx-1 text-danger"></i>
                                            @endif
                                            <a href="{!! $gear->category->url !!}">
                                                {!! $gear->category->name !!}
                                            </a>
                                        </p>
                                    </div>
                                @endif
                            </div>
                            @if ($gear->parent)
                                <h5 class="alert alert-secondary">
                                    Upgrade of:
                                    @if ($gear->parent->has_image)
                                        <img src="{{ $gear->parent->imageUrl }}" class="rounded world-entry-image" style="max-height: 50px;" />
                                    @endif
                                    {!! $gear->parent->displayName !!}
                                </h5>
                            @endif
                            {!! $description !!}
                            @if ($gear->children->count())
                                <h5 class="alert alert-info">Upgrades:</h5>
                                @foreach ($gear->children as $child)
                                    <div class="card">
                                        <h5 class="card-header border-bottom-0 inventory-header">
                                            <a class="inventory-collapse-toggle collapse-toggle collapsed" href="#{{ $child->id }}-drop-collapse" data-toggle="collapse">
                                                @if ($child->has_image)
                                                    <img src="{{ $child->imageUrl }}" class="rounded world-entry-image" style="max-height: 50px;" />
                                                @endif
                                                {!! $child->name !!}
                                            </a>
                                        </h5>
                                        <div class="collapse" id="{{ $child->id }}-drop-collapse">
                                            <div class="card-body py-0">
                                                @if (count(getLimits($child)))
                                                    @include('widgets._limits', [
                                                        'object' => $child,
                                                        'hideUnlock' => true,
                                                    ])
                                                @else
                                                    No upgrade cost set.
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            @if ($gear->stats?->count())
                                <h4>Stats</h4>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th width="70%">Stat</th>
                                            <th width="30%">Bonus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($gear->stats as $stat)
                                            <tr>
                                                <td>{!! $stat->stat->name !!}</td>
                                                <td>{{ $stat->count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm">
        </div>
    </div>
@endsection
