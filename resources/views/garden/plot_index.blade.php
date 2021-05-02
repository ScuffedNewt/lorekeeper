@extends('home.layout')

@section('home-title') Garden Plots @endsection

@section('home-content')
{!! breadcrumbs(['Garden' => 'garden', 'Plot' => 'plot',]) !!}

<h1>
    Available Plots
</h1>

<div class="card-deck">
@foreach($userPlots as $plot)
    <div class="card text-center" style="width: 200px; height: 200px; background-color: grey;">
        <h3 class="pt-2"> Plot #{{ $plot->id }}</h3>
        @if($user->gardenplots->contains('plot_id', $plot->id))
            You own this plot already!
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