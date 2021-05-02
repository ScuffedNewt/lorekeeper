@extends('layouts.app')

@section('title') Garden @endsection

@section('content')
<link rel="stylesheet" type="text/css" href="{{ url('/css/gardens.css') }}" />
    <div>
      <h3>Gardens</h3>
        <div class="row grid-container">
          <div class="col-4 grid-item">1</div>
          <div class="col-4 grid-item">2</div>
          <div class="col-4 grid-item">3</div>
          <div class="col-4 grid-item">4</div>
          <div class="col-4 grid-item">5</div>
          <div class="col-4 grid-item">6</div>
          <div class="col-4 grid-item">7</div>
          <div class="col-4 grid-item">8</div>
          <div class="col-4 grid-item">9</div>
        </div>
    </div>

    <!--------------------------------------------->
  <div class="row ml-2">
    @foreach($plots as $plot)
    <div class="card text-center" style="width: 200px; height: 200px; background-color: grey;">
        <img src="{{ $plot }}" class="my-auto" style="width: 150px; height:150px;">
        <!---- if user owns plot --->
            @if(Auth::user()->gardenplots->contains('plot_id', $plot->id))
                @php
                    $now = Carbon\Carbon::now();
                    $diff = $now->diffInMinutes($plot->end_at, false);
                @endphp
                @if($plot->end_at >= $now)
                    <div class="text-white">@if($diff >= 0 && $diff < 1) 1> minute till you finish gardening! @else {{ $diff }} minutes till you finish gardening!@endif</div>
                    <p>Started {!! pretty_date($plot->started_at)!!}
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
@endsection