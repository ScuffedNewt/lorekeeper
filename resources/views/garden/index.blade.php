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
    <div class="row bg-light overflow-auto card" style="overflow-x: scroll;">
        <div class=" p-2 btn-group btn-group-toggle" data-toggle="buttons" >
            @foreach($userModifiers as $modifier)
                @foreach($modifier->chunk(4) as $chunk)
                    @foreach($chunk as $stack)
                        <label type="button" class="modifier-btn btn btn-warning mr-2"  type="radio" autocomplete="off">
                            <input type="radio" name="options" autocomplete="off" value="{{$stack->first()->id}}" >
                            <img class="img-fluid image-zoom" src="{{ $stack->first()->imageUrl }}" style="height: 6em">
                            <div class="text-small font-weight-bold text-secondary"> {!!$stack->first()->name !!} <br>Quantity: x{{ $stack->sum('pivot.count') }}</div>
                        </label>
                    @endforeach
                @endforeach
            @endforeach
        </div>
    </div>
    <div class="row bg-light" style=" font-size: 100% !important">
        <div class="col-lg-10 col-md-9 card">
            <h5>Plots</h5>
            <div class="row ">
            @foreach($plots->chunk(3) as $chunk)
                @foreach($chunk as $plot)
                <div class="card text-center m-1 col-lg-3 col-md-4" style="width: 15em; height: 15em; background-color: rgb(100, 60, 41);">
                    <!---- if user owns plot --->
                        @if(Auth::user()->gardenplots->contains('plot_id', $plot->id))
                        @php
                            $userPlot = Auth::user()->gardenplots->where('plot_id', $plot->id)->first();
                            $now = Carbon\Carbon::now();
                        @endphp
                        <!--- If plant is dead show dead thing --->
                            @if($userPlot->is_dead)
                            <button type="button" class="btn btn-dark" style="width:15em; height :15em;" id="{{ $userPlot->id }}-button" onclick="clearPlot({{ $userPlot->id }})">
                                <img src="{{ asset('images/data/seeds/dead.png') }}" class="my-auto mx-auto" style="width: 5em; height:5em;">
                                <div class="text-danger">Your plant has died.
                                    <br> Click to clear.</div>
                            </button>
                        <!--- if not dead show not dead thing --->
                            @else
                                @if($userPlot->started_at != null)
                                    @if($userPlot->water_at > $now)
                                        <img src="{{ asset('images/data/seeds/seed.png') }}" class="my-auto mx-auto" style="width: 100px; height:100px;">
                                        <div id="{{$userPlot->id}}-water-text" class ="text-white">Next watering {{$userPlot->water_at}}</div>
                                        <p class="text-white">Started {!! pretty_date($userPlot->started_at)!!}
                                            
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

                                    @else
                                    <!--- if we can water, check if the plant is complete or if it's ready to be watered again -->
                                        @if($userPlot->watering == $userPlot->item->tags->where('tag', 'seed')->first()->data['waterings']))
                                            <img src="{{ asset('images/data/seeds/seed.png') }}" class="my-auto mx-auto" style="width: 100px; height:100px;">
                                            {!! Form::open(['url' => 'garden/claim/' . $userPlot->id]) !!}
                                            {!! Form::submit('Claim!', ['class' => 'btn btn-sm btn-primary']) !!}
                                            {!! Form::close() !!}
                                    <!---->
                                        @else
                                            <button type="button" class="btn btn-primary" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="waterPlot({{ $userPlot->id }})">
                                                <img src="{{ asset('images/data/seeds/seed.png') }}" class="my-auto mx-auto" style="width: 100px; height:100px;">
                                                Water <i class="fas fa-water"></i>
                                            </button>
                                        @endif
                                    @endif
                                @else
                                    <button type="button" class="btn btn-primary" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="plantSeed({{ $userPlot->id }})">
                                        Plant Seed!
                                    </button>
                                @endif
                            @endif
                    <!---- if not owned plot --->
                        @else
                            This plot {{ $plot->free ? 'is free!' : 'costs ' . $plot->plot_cost . ' ' . $plot->currency->name }}
                            {!! Form::open(['url' => 'garden/plots/purchase/' . $plot->id]) !!}
                            {!! Form::submit('Purchase plot?', ['class' => 'btn btn-sm btn-primary mt-3']) !!}
                            {!! Form::close() !!}
                        @endif
                    </div>
                @endforeach
            @endforeach
            </div>
        
        </div>
        <!-------------------- SEEDS -------------------------->
        <div class="col-md-3  col-lg-2 text-center card " style="overflow-y: scroll; ">
            <h5 class="text-left">Seeds</h5>
            <div class=" p-2 btn-group-vertical btn-group-toggle" id="seedSelector" data-toggle="buttons">
                @foreach($userSeeds as $seed)
                    @foreach($seed->chunk(4) as $chunk)
                        @foreach($chunk as $stack)
                            <label type="button" class="btn btn-secondary my-1 btn-block" type="radio" autocomplete="off">
                                <input type="radio" name="options" autocomplete="off" value="{{$stack->first()->id}}" >
                                    <img src="{{ $stack->first()->imageUrl }}" class="image-zoom" style="width: 100%">
                                <div class="text-small text-white text-center" > {!!$stack->first()->name !!} <br>Quantity: x{{ $stack->sum('pivot.count') }}</div>
                            </label>
                        @endforeach
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
    <script>
        let selectedSeed;
        let selectedModifier;
        
        $("#seedSelector input:radio").change(function() {
            selectedSeed = $(this).val();
        });

        $("#modifierSelector input:radio").change(function() {
            selectedModifier = $(this).val();
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
