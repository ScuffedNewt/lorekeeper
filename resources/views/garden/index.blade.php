@extends('layouts.app')

@section('title') Garden @endsection

@section('content')
<head>
    <link rel="stylesheet" type="text/css" href="{{ url('/css/gardens.css') }}" />
</head>

<h1 class="display-4">garden</h1>
<hr>
    <!--------------------------------------------->
<div class="container" id="content">
    <h5 class="h5">Modifiers</h5>
    <div class=" bg-light overflow-auto card" style="overflow-x: scroll;">
        <div class=" p-2 btn-group btn-group-toggle" id="modifierSelector" data-toggle="buttons" >
            <label type="button" class="modifier-btn btn mr-2 modifier-button" type="radio" autocomplete="off">
                <input type="radio" name="options" autocomplete="off" value=null >
                    <i class="fas fa-times fa-5x text-white"></i>
                    <div class="text-small font-weight-bold text-secondary">No Modifier</div>
            </label>
            @foreach($userModifiers as $modifier)
                @foreach($modifier->chunk(4) as $chunk)
                    @foreach($chunk as $stack)
                        <label type="button" class="modifier-btn btn  mr-2 modifier-button" type="radio" autocomplete="off">
                            <input type="radio" name="options" autocomplete="off" value="{{ $stack->first()->id }}" >
                            <img class="img-fluid image-zoom" src="{{ $stack->first()->imageUrl }}" style="height: 6em">
                            <div class="text-small font-weight-bold text-secondary"> {!! $stack->first()->name !!} <br>Quantity: x{{ $stack->sum('pivot.count') }}</div>
                        </label>
                    @endforeach
                @endforeach
            @endforeach
        </div>
    </div>
    <!--------------------------------------------->
    <div class="d-flex justify-content-between bg-light" style=" font-size: 100% !important  ">
        <div class="col card" style=" max-height:60em;">
            <h5 class="mt-1">Plots</h5>
            <div class="row ">
            @foreach($plots->chunk(3) as $chunk)
                @foreach($chunk as $plot)
                <div class="card text-center m-1 col-lg-3 col-md-4" style="min-width: 14em; min-height: 14em; background-color: rgb(100, 60, 41); justify-content: center;">
                    <!---- if user owns plot --->
                        @if(Auth::user()->gardenplots->contains('plot_id', $plot->id))
                        @php
                            $userPlot = Auth::user()->gardenplots->where('plot_id', $plot->id)->first();
                            $now = Carbon\Carbon::now();
                        @endphp
                        <!--- If plant is dead show dead thing --->
                            @if($userPlot->is_dead)
                                <button type="button" class="btn btn-dark" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="clearPlot({{ $userPlot->id }})">
                                    <img class="img-fluid my-auto mx-auto pt-2" src="{{ asset('images/data/seeds/dead.png') }}" style="width: 5em; height:5em;">
                                    <div class="text-danger">Your plant has died.
                                    <br> Click to clear.</div>
                                </button>
                        <!--- if not dead show not dead thing --->
                            @else
                                @if($userPlot->started_at != null)
                                <!----If there is a plant growing --->
                                    @if($userPlot->water_at > $now)
                                        <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ asset('images/data/seeds/seed.png') }}" data-toggle="tooltip" title="{{ $userPlot->waterings }} {{ $userPlot->waterings == 1 ? 'watering' : 'waterings' }} done!" style="width: 10em; height: 10em;">
                                        <div id="{{ $userPlot->id }}-water-text" class ="text-white">Next watering {{ $userPlot->water_at }}</div>
                                        <p class="text-white">Started {!! pretty_date($userPlot->started_at)!!}
                                        <!---- Water times -->
                                        <script>
                                            function waterCount(id, timer) {
                                                setInterval(function(){ 
                                                    
                                                var date = new Date(timer-=1000);
                                                var text = "Next watering in "+ date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
                                                $("#"+id+"-water-text").text(text);
                                            }, 1000)}

                                            var wateringLeft = Date.parse("<?php echo $userPlot->water_at ?>");
                                            var waterTextId = "<?php echo $userPlot->id ?>"
                                        
                                            setInterval(waterCount(waterTextId, wateringLeft), 1000);
                                        </script>
                                        <!------------------>
                                    @else
                                    <!--- if we can water, check if the plant is complete or if it's ready to be watered again -->
                                        @if($userPlot->watering == $userPlot->item->tags->where('tag', 'seed')->first()->data['waterings']))
                                            <img class="img-fluid my-auto mx-auto pt-2" src="{{ asset('images/data/seeds/seed.png') }}" style="width: 10em; height:10em;">
                                            {!! Form::open(['url' => 'garden/claim/' . $userPlot->id]) !!}
                                            {!! Form::submit('Claim!', ['class' => 'btn btn-sm btn-primary']) !!}
                                            {!! Form::close() !!}
                                    <!---->
                                        @else
                                            <button type="button" class="btn btn-primary" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="waterPlot({{ $userPlot->id }})">
                                                <img class="img-fluid my-auto mx-auto pt-2" src="{{ asset('images/data/seeds/seed.png') }}" style="width: 10em; height: 10em;">
                                                <br> Water <i class="fas fa-water"></i>
                                            </button>
                                        @endif
                                    @endif
                                @else
                                <!----If there is NO plant growing --->
                                <i class="fas fa-seedling text-white m-1"></i>
                                    @if($userPlot->modifiers != null)
                                            @php $mods = json_decode($userPlot->modifiers, true); @endphp
                                            @if($mods['fertiliser'])
                                                <p class="text-white"><i class="fas fa-star"></i> Fertiliser Quality: {{ $mods['fertiliser'] }}</p>
                                            @endif
                                    @endif

                                    <button id="seed" type="button" class="btn btn-success hide" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="plantSeed({{ $userPlot->id }})">
                                        Plant Seed!
                                    </button>
                                    <button id="mod" type="button" class="btn btn-warning text-white hide" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="addModifier({{ $userPlot->id }})">
                                        Apply Modifier!
                                    </button>
                                @endif
                            @endif
                    <!---- if not owned plot --->
                        @else
                            <div class="p-5 container text-white">
                                This plot {{ $plot->free ? 'is free!' : 'costs ' . $plot->plot_cost . ' ' . $plot->currency->name }}
                                {!! Form::open(['url' => 'garden/plots/purchase/' . $plot->id]) !!}
                                {!! Form::submit('Purchase plot?', ['class' => 'btn btn-sm btn-primary mt-3']) !!}
                                {!! Form::close() !!}
                            </div>
                        @endif
                    </div>
                @endforeach
            @endforeach
            </div>
        
        </div>
        <!-------------------- SEEDS -------------------------->
        <div class="text-center card" style="overflow-y: scroll; overflow-x: hidden; flex 0 0 8em; min-width: 11em; max-width: 11em; max-height:60em;">
            <h5 class="text-left">Seeds</h5> 
            <div class=" p-2 btn-group-vertical btn-group-toggle" id="seedSelector" data-toggle="buttons">
                <label type="button" class="btn  my-1 btn-block seed-button" type="radio" autocomplete="off" >
                    <input type="radio" name="options" autocomplete="off" value=null >
                        <i class="fas fa-times fa-5x text-white"></i>
                        <div class="text-small text-white text-center" style="font-size:1rem;">No Seed</div>
                </label>
                @foreach($userSeeds as $seed)
                    @foreach($seed->chunk(4) as $chunk)
                        @foreach($chunk as $stack)
                            <label type="button" class="btn  my-1 btn-block seed-button" type="radio" autocomplete="off">
                                <input type="radio" name="options" autocomplete="off" value="{{$stack->first()->id}}" >
                                    <img class="img-fluid image-zoom"  src="{{ $stack->first()->imageUrl }}" style="width: 100%">
                                <div class="text-small text-white text-center  text-wrap" style="font-size:1rem;"> {!!$stack->first()->name !!} <br>Quantity: <br> x{{ $stack->sum('pivot.count') }}</div>
                            </label>
                        @endforeach
                    @endforeach
                @endforeach
            </div>
        </div>
        <!--------------------------------------------->
    </div>
</div>
@endsection
@section('scripts')
    <script>
        let selectedSeed;
        let selectedModifier;
        
        $("#seedSelector input:radio").change(function() {
            selectedSeed = $(this).val();
            $("#seed").removeClass('hide');
            $("#mod").addClass('hide');
        });

        $("#modifierSelector input:radio").change(function() {
            selectedModifier = $(this).val();
            $("#mod").removeClass('hide');
            $("#seed").addClass('hide');
        });

        function plantSeed(plotID) {
            if(selectedSeed == null){
                alert("Please select a seed!");
            }
            else {
                $.ajax({
                url: "{{ url('garden/plant') }}",
                method: 'post',
                data:{
                    _token: '{{ csrf_token() }}',
                    plotID:plotID,
                    seedID:selectedSeed
                    },
                dataType: 'json',
                success: function(data) {
                    $("body").load(" #app");
                },
                error: function(error) {
                    console.log(error.responseJSON);
                    alert("AJAX call failed: " +  error);
                    $("body").load(" #app");
                }
            });
            }
        }

        function addModifier(plotID) {
            if(selectedModifier == null){
                alert("Please select a modifier!");
            }
            else {
                $.ajax({
                url: "{{ url('garden/mod') }}",
                method: 'post',
                data:{
                    _token: '{{ csrf_token() }}',
                    plotID:plotID,
                    modID:selectedModifier
                    },
                dataType: 'json',
                success: function(data) {
                    $("body").load(" #app");
                },
                error: function(error) {
                    console.log(error);
                    alert("AJAX call failed: " + error);
                    $("body").load(" #app");
                }
            });
            }
        }

        function waterPlot(plotID) {
            $.ajax({
                url: "{{ url('garden/water') }}",
                method: 'post',
                data:{
                    _token: '{{ csrf_token() }}',
                    plotID:plotID,
                    },
                dataType: 'json',
                success: function(data) {
                    $("body").load(" #app");
                },
                error: function(error) {
                    console.log(error);
                    alert("AJAX call failed: " + error);
                    $("body").load(" #app");
                }
            });
        }

        function clearPlot(plotID) {
            $.ajax({
                url: "{{ url('garden/clear') }}",
                method: 'post',
                data:{
                    _token: '{{ csrf_token() }}',
                    plotID:plotID,
                    },
                dataType: 'json',
                success: function(data) {
                    $("body").load(" #app");
                },
                error: function(error) {
                    console.log(error);
                    alert("AJAX call failed: " + error);
                    $("body").load(" #app");
                }
            });
        }

    </script>
@endsection
