@if ($season)
    {!! Form::open(['url' => 'admin/weather/seasons/delete/' . $season->id]) !!}

    <p>You are about to delete the season <strong>{{ $season->name }}</strong>. This is not reversible. If the site is currently in this season, you will not be able to delete it without changing the season first.</p>
    <p>Are you sure you want to delete <strong>{{ $season->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Season', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid season selected.
@endif
