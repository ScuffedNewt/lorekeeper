@extends('gardens.layout')

@section('title')
    Gardens
@endsection

@section('head')
    <link href="{{ asset('css/gardens.css?v=' . filemtime(public_path('css/gardens.css'))) }}" rel="stylesheet">
@endsection

@section('content')
    {!! breadcrumbs(['Gardens' => 'gardens']) !!}

    <h1>Gardens</h1>

    <noscript>
        <div class="alert alert-danger">
            <strong>Warning!</strong> You need to enable JavaScript to use this minigame.
        </div>
    </noscript>

    @if (!count($groups))
        <p>There are no gardens yet.</p>
    @else
        @include('gardens._garden', [
            'user' => Auth::user(),
            'readOnly' => false,
            'groups' => $groups,
            'isGardenUnlocked' => $isGardenUnlocked,
        ])
    @endif
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
