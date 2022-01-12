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
            <li class="nav-item">
                <a class="nav-link" id="coopTab" data-toggle="tab" href="#coop" role="tab">Coop</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="barnTab" data-toggle="tab" href="#barn" role="tab">Barn</a>
            </li>
        </ul>
    </div>
    <script>
        const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    </script>
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="farm">
            @include('garden._garden', ['userSeeds' => $userSeeds, 'userModifiers' => $userModifiers, 'plots' => $plots->where('plot_type', 'Seed')])
        </div>
        <div class="tab-pane fade" id="coop">
            @include('garden._coop', ['userCoops' => $userCoops, 'userModifiers' => $userModifiers, 'plots' => $plots->where('plot_type', 'Coop')])
        </div>
        <div class="tab-pane fade" id="barn">
            @include('garden._barn', ['userBarns' => $userBarns, 'userModifiers' => $userModifiers, 'plots' => $plots->where('plot_type', 'Barn')])
        </div>
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
