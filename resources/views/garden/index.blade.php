@extends('layouts.app')

@section('title') Garden @endsection

@section('content')
<head>
    <link rel="stylesheet" type="text/css" href="{{ url('/css/gardens.css') }}" />
</head>

<h1>garden</h1>
<hr>
    <!--------------------------------------------->
<div class="container" id="content">
    <div class="row col-12">
        <div class="col-1">
        </div>
        <div class="col-9">
            <h3>Plots</h3>
            @foreach($plots->chunk(3) as $chunk)
            <div class="row ml-2">
                @foreach($chunk as $plot)
                <div class="card text-center m-1" style="width: 200px; height: 200px; background-color: rgb(100, 60, 41);">
                    <!---- if user owns plot --->
                        @if(Auth::user()->gardenplots->contains('plot_id', $plot->id))
                            @php
                                $userPlot = Auth::user()->gardenplots->where('plot_id', $plot->id)->first();
                                $now = Carbon\Carbon::now();
                            @endphp
                            @if($userPlot->started_at != null)
                                @if($userPlot->water_at > $now)
                                    <img src="{{ asset('images/data/seeds/seed.png') }}" class="my-auto mx-auto" style="width: 100px; height:100px;">
                                    <div class="text-white">Next watering {!! pretty_date($userPlot->water_at) !!}</div>
                                    <p class="text-white">Started {!! pretty_date($userPlot->started_at)!!}
                                @else
                                <!--- if we can water, check if the plant is complete or if it's ready to be watered again -->
                                    @if($userPlot->watering == $userPlot->item->tags->where('tag', 'seed')->first()->data['waterings']))
                                        <img src="{{ asset('images/data/seeds/seed.png') }}" class="my-auto mx-auto" style="width: 100px; height:100px;">
                                        {!! Form::open(['url' => 'garden/claim/' . $userPlot->id]) !!}
                                        {!! Form::submit('Claim!', ['class' => 'btn btn-sm btn-primary']) !!}
                                        {!! Form::close() !!}
                                    @else
                                        <img src="{{ asset('images/data/seeds/seed.png') }}" class="my-auto mx-auto" style="width: 100px; height:100px;">
                                        <button type="button" class="btn btn-primary" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="waterPlot({{ $userPlot->id }})">
                                            Water <i class="fas fa-water"></i>
                                        </button>
                                    @endif
                                @endif
                            @else
                                <button type="button" class="btn btn-primary" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="plantSeed({{ $userPlot->id }})">
                                    Plant Here
                                </button>
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
            </div>
            @endforeach
        </div>
        <div class="col-2 text-center">
            <h4>Seeds</h4>
            <div class="bg-dark p-2 btn-group-vertical btn-group-toggle" id="seedSelector" data-toggle="buttons" style="overflow-y: scroll;">
                @foreach($userSeeds as $seed)
                    @foreach($seed->chunk(4) as $chunk)
                        @foreach($chunk as $stack)
                            <label type="button" class="btn btn-secondary my-1" type="radio" autocomplete="off">
                                <input type="radio" name="options" autocomplete="off" value="{{$stack->first()->id}}" >
                                    <img src="{{ $stack->first()->imageUrl }}" style="width: 100%">
                                <div class="text-small text-white"> {!!$stack->first()->name !!} <br>Quantity: x{{ $stack->sum('pivot.count') }}</div>
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
        
        $("#seedSelector input:radio").change(function() {
            selectedSeed = $(this).val();
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
    </script>
@endsection
