@if ($runbook)
    {!! Form::open(['url' => 'admin/runbooks/delete/' . $runbook->id]) !!}

    <p>You are about to delete the runbook <strong>{{ $runbook->name }}</strong>. This is not reversible. If you would like to preserve the content while preventing users from accessing the runbook, you can use the viewable setting instead to hide the runbook.
    </p>
    <p>Are you sure you want to delete <strong>{{ $runbook->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Runbook', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid runbook selected.
@endif
