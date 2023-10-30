<tr>
    <td>
        <a href="{{ $pair->character_1->url }}">
            <img class="rounded" src="{{ $pair->character_1->image->thumbnailUrl }}" style="max-width: 75px;" />
        </a>
        <br>
        {!! $pair->character_1->displayName !!}
    </td>
    <td>
        <a href="{{ $pair->character_2->url }}">
            <img class="rounded" src="{{ $pair->character_2->image->thumbnailUrl }}" style="max-width: 75px;" />
        </a>
        <br>
        {!! $pair->character_2->displayName !!}
    </td>
    <td>
        {!! $pair->displayItems !!}
    </td>
    <td>
        <span class="btn btn-{{ $pair->status == 'PENDING' ? 'secondary' : ($pair->status == 'APPROVED' ? 'success' : ($pair->status == 'COMPLETE' ? 'secondary' : 'danger')) }}">{{ $pair->status }}</span>
    </td>
    @if (Request::get('type') != 'closed')
        <td>
            @if (Request::get('type') == 'waiting')
                @if ($pair->status == 'APPROVED')
                    {!! Form::open(['url' => 'characters/pairings/complete/' . $pair->id]) !!}
                    {!! Form::submit('Create MYO(s)', ['class' => 'btn btn-primary mb-2']) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['url' => 'characters/pairings/cancel/' . $pair->id]) !!}
                    {!! Form::submit('Cancel', ['class' => 'btn btn-danger']) !!}
                    {!! Form::close() !!}
                @else
                    {!! Form::open(['url' => 'characters/pairings/cancel']) !!}
                    {!! Form::submit('Cancel', ['class' => 'btn btn-danger']) !!}
                    {!! Form::close() !!}
                    <a href="#" class="btn btn-secondary disabled">Create MYO(s)</a>
                @endif
            @endif
            @if (Request::get('type') == 'approval')
                @if ($pair->user_id == Auth::user()->id)
                    <p>Waiting on other user to approve.</p>
                    {!! Form::open(['url' => 'characters/pairings/cancel/' . $pair->id]) !!}
                    {!! Form::submit('Cancel', ['class' => 'btn btn-danger']) !!}
                    {!! Form::close() !!}
                @else
                    <p>This pairing requires your approval to complete.</p>
                    <div class="row">
                        {!! Form::open(['url' => 'characters/pairings/reject/' . $pair->id]) !!}
                        {!! Form::submit('Reject', ['class' => 'btn btn-danger mx-2']) !!}
                        {!! Form::close() !!}

                        {!! Form::open(['url' => 'characters/pairings/approve/' . $pair->id]) !!}
                        {!! Form::submit('Approve', ['class' => 'btn btn-success']) !!}
                        {!! Form::close() !!}
                    </div>
                @endif
            @endif
        </td>
    @endif
    <td>
        {!! pretty_date($pair->created_at) !!}
    </td>
</tr>
