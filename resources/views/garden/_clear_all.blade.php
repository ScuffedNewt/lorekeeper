<div class="text-right justify content-right">
    {!! Form::open(['url' => 'garden/all/' . $type]) !!}
    {!! Form::submit(($type == 'seed' ? 'Water' : 'Feed') . ' All', ['class' => 'btn btn-lg btn-primary m-1']) !!}
    {!! Form::close() !!}
</div>
<div class="text-right justify content-right">
    {!! Form::open(['url' => 'garden/collect/' . $type]) !!}
    {!! Form::submit('Collect All', ['class' => 'btn btn-lg btn-success m-1']) !!}
    {!! Form::close() !!}
</div>
