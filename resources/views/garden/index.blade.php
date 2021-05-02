@extends('layouts.app')

@section('title') Garden @endsection

@section('content')
<link rel="stylesheet" type="text/css" href="{{ url('/css/gardens.css') }}" />
<h1>garden</h1>
<hr>
    <!--------------------------------------------->
<div class="container">
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
                                $now = Carbon\Carbon::now();
                                $diff = $now->diffInMinutes($plot->end_at, false);
                            @endphp
                            @if($plot->end_at >= $now)
                                <img src="{{ asset('images/data/seeds/seed.png') }}" class="my-auto mx-auto" style="width: 100px; height:100px;">
                                <div class="text-white">@if($diff >= 0 && $diff < 1) 1> minute till you finish gardening! @else {{ $diff }} minutes till you finish gardening!@endif</div>
                                <p>Started {!! pretty_date($plot->started_at)!!}
                            @elseif($plot->end_at == null)
                                <button 
                                    type="button" 
                                    class="btn btn-primary" style="width:100%; height :100%;" 
                                    id="button" 
                                    onclick="plantSeed({{Auth::user()->gardenplots->where('plot_id', $plot->id)->first()->id}})"
                                >Plant Here</button>
                            @else
                                {!! Form::open(['url' => 'garden/claim/' . $plot->id]) !!}
                                {!! Form::submit('Claim!', ['class' => 'btn btn-sm btn-primary']) !!}
                                {!! Form::close() !!}
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
            console.log(selectedSeed);
        });


        function plantSeed(plotID){
            if(selectedSeed == null){
                console.log("bruh pls, choose a seed first")
            }
            else{
                console.log(plotID);
                console.log(selectedSeed);

                $.ajax({
                    type: "POST", 
                    url:"/garden/plant",
                    data:{
                        plotID:plotID,
                        seedID:selectedSeed
                    },
                    success:function(data){
                        alert(data.success);
                    }
                });
            }
        }

    </script>


@endsection
