@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Garden
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Garden' => $user->url . '/garden']) !!}

    <h1>
        {!! $user->displayName !!}'s Garden
    </h1>

    <div>
        @include('gardens._garden', [
            'user'     => $user,
            'readOnly' => $readonly,
            'groups'   => $groups,
            'is_garden_unlocked' => $is_garden_unlocked,
        ])
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