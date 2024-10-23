@extends('layouts.app')

@section('title')
    Gardens
@endsection

@section('content')
    {!! breadcrumbs(['Home' => '/', 'Gardens' => 'gardens']) !!}

    <head>
        <link href="{{ asset('css/gardens.css?v=' . filemtime(public_path('css/gardens.css'))) }}" rel="stylesheet">
    </head>

    <h1>Gardens</h1>
    <p>The gardens are a rich place full of life. In it, several species of plants and animals can be found...</p>
    <noscript>
        <div class="alert alert-danger">
            <strong>Warning!</strong> You need to enable JavaScript to use this minigame.
        </div>
    </noscript>

    @if (Settings::get('garden_view'))
        <div class="text-right mb-3">
            <div class="btn-group">
                <button type="button" class="btn btn-secondary active nav-view-button" data-toggle="tooltip" title="Nav View" alt="Nav View"><i class="fas fa-bars"></i></button>
                <button type="button" class="btn btn-secondary grid-view-button" data-toggle="tooltip" title="Grid View" alt="Grid View"><i class="fas fa-th"></i></button>
            </div>
        </div>
    @endif

    <div id="navView" class="{{ Settings::get('garden_view') ? 'hide' : (config('lorekeeper.garden.garden_view') == 'nav' ? '' : 'hide') }}">
        <div class="card character-bio">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    @foreach ($groups as $group => $gardens)
                        @foreach ($gardens as $garden)
                            <li class="nav-item">
                                <a class="nav-link {{ $loop->first && $group == 'unlocked' ? 'active' : '' }}" id="gardenTab-{{ $garden->id }}" data-toggle="tab" href="#{{ strtolower(str_replace(' ', '-', $garden->name)) }}" role="tab">
                                    {!! $is_garden_unlocked[$garden->id] ? '' : '<i class="fas fa-lock"></i> ' !!}
                                    @if ($garden->has_image)
                                        <img src="{{ $garden->imageUrl }}" class="img-fluid" style="max-height: 3em;">
                                    @endif
                                    {{ $garden->name }}
                                </a>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
            <div class="card-body tab-content">
                @foreach ($groups as $group => $gardens)
                    @foreach ($gardens as $garden)
                        <div class="tab-pane fade {{ $loop->first && $group == 'unlocked' ? 'show active' : '' }}" id="{{ strtolower(str_replace(' ', '-', $garden->name)) }}">
                            @if ($is_garden_unlocked[$garden->id])
                                @include('gardens._panel', [
                                    'garden' => $garden,
                                    'user_garden_objects' => $garden->getUserGardenObjects(Auth::user()),
                                ])
                            @else
                                <div class="alert alert-danger">This garden is locked.</div>
                                @include('widgets._limits', ['object' => $garden])
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
    <div id="gridView" class="{{ Settings::get('garden_view') ? (config('lorekeeper.garden.garden_view') == 'grid' ? '' : 'hide') : 'hide' }}">
        @foreach ($groups as $group => $gardens)
            <div class="card text-center h-100">
                <div class="card-header">
                    <h3>{{ $group }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
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
@endsection
@section('scripts')
    @parent
    @if (Settings::get('garden_view'))
        @include('gardens.js._view_js')
    @endif
    @include('gardens.js._garden_js')
    <script>
        $(document).ready(function() {
            $('.userselectize').selectize();
            // vvv
            if (location.hash) {
                $("a[href='" + location.hash + "']").tab("show");
            }
            $(document.body).on("click", "a[data-toggle='tab']", function(event) {
                location.hash = this.getAttribute("href");
            });
        });
        // stay tabbed in on selected pane
        $(window).on("popstate", function() {
            var anchor = location.hash || $("a[data-toggle='tab']").first().attr("href");
            $("a[href='" + anchor + "']").tab("show");
        });
    </script>
@endsection
