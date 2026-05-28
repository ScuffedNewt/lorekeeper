<div class="perished-plot">
    {!! Form::open(['url' => 'gardens/clear/' . $userPlot->id]) !!}
    <img class="img-fluid my-auto mx-auto pt-2" src="{{ asset('images/garden/dead_seed.png') }}" style="width: 7em; height:7em;">
    <div class="text-danger pt-2">This seed has perished.</div>
    {!! Form::button('Click to Clear', ['class' => 'btn btn-danger', 'type' => 'submit']) !!}
    {!! Form::close() !!}
</div>
