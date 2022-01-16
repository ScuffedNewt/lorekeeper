@extends('layouts.app')

@section('title') Gardens @endsection

@section('content')
<head>
    <link rel="stylesheet" type="text/css" href="{{ url('/css/gardens.css') }}" />
</head>
<h1>Gardens</h1>
<p>The gardens are a rich place full of life. In it, several species of plants and animals can be found...</p>
<!-- Nav tabs -->
<div class="character-bio">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link active" id="farmTab" data-toggle="tab" href="#farm" role="tab">Farm</a>
            </li>
            @if(count($plots->where('plot_type', 'Coop')) > 0)
            <li class="nav-item">
                <a class="nav-link" id="coopTab" data-toggle="tab" href="#coop" role="tab">Coop</a>
            </li>
            @endif
            @if(count($plots->where('plot_type', 'Barn')) > 0)
            <li class="nav-item">
                <a class="nav-link" id="barnTab" data-toggle="tab" href="#barn" role="tab">Barn</a>
            </li>
            @endif
        </ul>
    </div>
    <script>
        let selectedItem;
        let selectedAnimal;
        let selectedModifier;
        const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        function waterCount(id, timer, type = 'seed') {
            setInterval(function(){ 
                
            var date = new Date(timer);
            var now = new Date();
            // count down time difference between now and date
            var diff = date.getTime() - now.getTime();
            var time = new Date(diff);

            var seconds = time.getUTCSeconds();
            if(seconds < 10) seconds = "0" + seconds;
            var minutes = time.getUTCMinutes();
            if(minutes < 10) minutes = "0" + minutes;
            var hours = time.getUTCHours();
            if(hours < 10) hours = "0" + hours;

            var text = "Next " + (type != 'seed' ? 'feeding' : 'watering') + " in "+ hours + ":" + minutes + ":" + seconds;
            $("#"+id+"-water-text").text(text);
            }, 1000);
        }
    </script>
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="Seed">
            @include('garden._panel', ['userItems' => $userSeeds, 'userModifiers' => $userModifiers, 'plots' => $plots->where('plot_type', 'Seed')])
        </div>
        @if(count($plots->where('plot_type', 'Coop')) > 0)
        <div class="tab-pane fade" id="coop">
            @include('garden._panel', ['userItems' => $userCoops, 'plots' => $plots->where('plot_type', 'Coop')])
        </div>
        @endif
        @if(count($plots->where('plot_type', 'Barn')) > 0)
        <div class="tab-pane fade" id="barn">
            @include('garden._panel', ['userItems' => $userBarns, 'plots' => $plots->where('plot_type', 'Barn')])
        </div>
        @endif
    </div>
</div>

@endsection
@section('scripts')
<script>
$(document).ready(function() {
    if (location.hash) {
        $("a[href='" + location.hash + "']").tab("show");
    }
    $(document.body).on("click", "a[data-toggle='tab']", function(event) {
        location.hash = this.getAttribute("href");
    });
});
$(window).on("popstate", function() {
    var anchor = location.hash || $("a[data-toggle='tab']").first().attr("href");
    $("a[href='" + anchor + "']").tab("show");
});
</script>
@endsection
