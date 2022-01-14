<div class="card mb-3">
    <div class="card-header h3">Friends Requests</div>
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
                                    {!! $request->displayName($user->id) !!} @if($request->recipient_id != $user->id) (Outgoing) @endif
                                </div>
                            </div>
                            @if($request->recipient_id == $user->id)
                                <div class="col-12 col-md-2 row">
                                    <div class="logs-table-cell">
                                        {!! Form::open(['url' => 'friends/deny/'.$request->id]) !!}
                                            {!! Form::submit('Deny', ['class' => 'btn btn-danger btn-sm', 'data-toggle' => 'tooltip', 'title' => 'Click here to deny this request. The sender will not be notified.']) !!}
                                        {!! Form::close() !!}
                                    </div>
                                    <div class="logs-table-cell">
                                        {!! Form::open(['url' => 'friends/accept/'.$request->id]) !!}
                                            {!! Form::submit('Accept', ['class' => 'btn btn-success btn-sm']) !!}
                                        {!! Form::close() !!}
                                    </div>
                                </div>
                            @else
                                <div class="col-12 col-md-2 row">
                                    <div class="text-info mt-1">Pending...</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="text-center">No friends requests (yet!)</p>
    @endif
    </div>
</div>