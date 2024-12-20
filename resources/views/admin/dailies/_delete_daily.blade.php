@if ($daily)
    {!! Form::open(['url' => 'admin/data/dailies/delete/' . $daily->id]) !!}

    <p>You are about to delete the {{ __('dailies.daily') }} <strong>{{ $daily->name }}</strong>. This is not reversible. If you would like to hide the {{ __('dailies.daily') }} from users, you can set it as inactive on its page.</p>
    <p>Are you sure you want to delete <strong>{{ $daily->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete ' . __('dailies.daily'), ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid daily selected.
@endif
