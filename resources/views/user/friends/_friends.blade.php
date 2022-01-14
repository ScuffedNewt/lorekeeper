<div class="card mb-3">
    <div class="card-header h3">Friends</div>
    <div class="card-body">
    @if($friends->count())
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-12 col-md-12"><div class="logs-table-cell">Name</div></div>
                </div>
            </div>    
            <div class="logs-table-body">
                @foreach($friends as $request)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-12 col-md-10">
                                <div class="logs-table-cell">
                                    {!! $request->displayName($user->id) !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-2 row">
                                <div class="logs-table-cell">
                                    {!! Form::open(['url' => 'friends/remove/'.$request->id]) !!}
                                        {!! Form::submit('Remove Friend', ['class' => 'btn btn-danger btn-sm', 'data-toggle' => 'tooltip', 'title' => 'Click here to remove this friend. They will not be notified.']) !!}
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="text-center">No friends (yet!)</p>
    @endif
    </div>
</div>