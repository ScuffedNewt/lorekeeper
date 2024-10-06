@extends('layouts.app')

@section('title')
    Gardens
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

    <div class="row">
        @foreach ($gardens as $garden)
            <div class="col-md-6">
                <div class="card p-3 text-center h-100">
                    @if ($garden->has_image)
                        <a href="{{ $garden->imageUrl }}" data-lightbox="entry" data-title="{{ $garden->name }}">
                            <img src="{{ $garden->imageUrl }}" class="img-fluid">
                        </a>
                    @else
                        <div class="alert alert-info">No image set.</div>
                    @endif
                    <h2>{{ $garden->name }}</h2>
                    <a href="{{ url('gardens/' . $garden->id) }}" class="btn btn-primary">View {{ $garden->name }}</a>
                </div>
            </div>
        @endforeach
    </div>

@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
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

        function getServerTime() {
            // ajax get call to get the time
            // check if window is focused otherwise don't update
            if (!document.hasFocus()) return;
            $.ajax({
                url: '{{ url('time') }}',
                type: 'GET',
                success: function(data) {
                    // update the time
                    now = new Date(data);
                }
            });
        }
    </script>
@endsection
