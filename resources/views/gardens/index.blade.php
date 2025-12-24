@extends('layouts.app')

@section('title')
    Gardens
@endsection

@section('head')
    <link href="{{ asset('css/gardens.css?v=' . filemtime(public_path('css/gardens.css'))) }}" rel="stylesheet">
@endsection

@section('content')
    {!! breadcrumbs(['Home' => '/', 'Gardens' => 'gardens']) !!}

    <h1>Gardens</h1>
    <p>The gardens are a rich place full of life. In it, several species of plants and animals can be found...</p>
    <noscript>
        <div class="alert alert-danger">
            <strong>Warning!</strong> You need to enable JavaScript to use this minigame.
        </div>
    </noscript>

    @include('gardens._garden', [
        'user'               => Auth::user(),
        'readOnly'           => false,
        'groups'             => $groups,
        'is_garden_unlocked' => $is_garden_unlocked,
    ])
@endsection
@section('scripts')
    @parent
    @include('gardens.js._garden_js')
    <script>
        $(document).ready(function() {
            $('.userselectize').selectize();
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
