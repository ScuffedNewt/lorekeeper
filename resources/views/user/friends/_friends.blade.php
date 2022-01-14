<div class="card mb-3">
    <div class="card-header h3">Friends</div>
    <div class="table-responsive">
        @if($friends->count())
        <table class="table table-sm mb-0">
            <thead>
                <th></th>
                <th></th>
            </thead>
            <tbody>
                @foreach($friends as $friend)
                    <tr>
                        <td class="text-center">{!! $friend->displayName($user->id) !!}</td>
                    </tr>
                    <tr>
                        <td class="text-center">
                            {!! Form::open(['url' => 'friends/remove/'.$friend->id]) !!}
                            {!! Form::submit('Remove Friend', ['class' => 'btn btn-danger btn-sm mx-2', 'data-toggle' => 'tooltip', 'title' => 'Click here to remove this friend. They will not be notified.']) !!}
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="card-body">
            <p class="text-center">No friends (yet!).</p>
        @endif
    </div>
</div>