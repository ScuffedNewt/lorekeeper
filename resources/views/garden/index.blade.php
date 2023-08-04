@extends('layouts.app')

@section('title')
    Gardens
@endsection

@section('content')

    <head>
        <link rel="stylesheet" type="text/css" href="{{ url('/css/gardens.css') }}" />
    </head>
    <h1>Gardens</h1>
    <p>The gardens are a rich place full of life. In it, several species of plants and animals can be found...</p>
    <noscript>
        <div class="alert alert-danger">
            <strong>Warning!</strong> You need to enable JavaScript to use this minigame.
        </div>
    </noscript>

    <!-- Nav tabs -->
    @if (!count($plots))
        <div class="alert alert-danger">There are currently no plots.</div>
    @else
        <div class="character-bio">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="farmTab" data-toggle="tab" href="#farm" role="tab">Farm</a>
                    </li>
                    @if (count($plots->where('plot_type', 'coop')) > 0)
                        <li class="nav-item">
                            <a class="nav-link" id="coopTab" data-toggle="tab" href="#coop" role="tab">Coop</a>
                        </li>
                    @endif
                    @if (count($plots->where('plot_type', 'barn')) > 0)
                        <li class="nav-item">
                            <a class="nav-link" id="barnTab" data-toggle="tab" href="#barn" role="tab">Barn</a>
                        </li>
                    @endif
                </ul>
            </div>
            <script>
                let selectedItem;
                let selectedBarn;
                let selectedCoop;
                let selectedModifier;
                let now = new Date("<?php echo date('Y-m-d H:i:s'); ?>");
                const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

                function waterCount(id, timer, type = 'seed') {
                    setInterval(function() {

                        var date = new Date(timer);
                        getServerTime();
                        // count down time difference between now and date
                        var diff = date.getTime() - now.getTime();
                        var time = new Date(diff);

                        var seconds = time.getUTCSeconds();
                        if (seconds < 10) seconds = "0" + seconds;
                        var minutes = time.getUTCMinutes();
                        if (minutes < 10) minutes = "0" + minutes;
                        var hours = time.getUTCHours();
                        if (hours < 10) hours = "0" + hours;

                        var text = "Next " + (type != 'seed' ? 'feeding' : 'watering') + " in " + hours + ":" + minutes + ":" + seconds;
                        $("#" + id + "-water-text").text(text);
                    }, 1000);
                }
            </script>
            <div class="card-body tab-content">
                <div class="tab-pane fade show active" id="farm">
                    <p>Quality on seeds refer to the minimum fertiliser level needed to successfully plant the seed.</p>
                    <p>Don't forget to water your plants!</p>
                    @include('garden._panel', ['userItems' => $userSeeds, 'userModifiers' => $userModifiers, 'plots' => $plots->where('plot_type', 'seed')])
                </div>
                @if (count($plots->where('plot_type', 'coop')) > 0)
                    <div class="tab-pane fade" id="coop">
                        @include('garden._panel', ['userItems' => $userCoops, 'plots' => $plots->where('plot_type', 'coop')])
                    </div>
                @endif
                @if (count($plots->where('plot_type', 'barn')) > 0)
                    <div class="tab-pane fade" id="barn">
                        @include('garden._panel', ['userItems' => $userBarns, 'plots' => $plots->where('plot_type', 'barn')])
                    </div>
                @endif
            </div>
        </div>
    @endif

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
