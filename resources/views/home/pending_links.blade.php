@extends('home.layout')

@section('home-title')
    Character Link Requests
@endsection

@section('home-content')
    {!! breadcrumbs(['Characters' => 'characters', 'Character Link Requests' => 'link-requests']) !!}

    <h1>
        Character Link Requests
    </h1>

    <div class="row">
        <div class="col-12 col-md-6">
            <h3>Incoming Requests</h3>
            <div class="card">
                <ul class="list-group list-group-flush">
                    @if ($incomings->count())
                        @foreach ($incomings as $incoming)
                            <li class="list-group-item text-center">
                                <div class="mb-2">
                                    Request from {!! $incoming->getOtherCharacterForUser(Auth::user()->id)->displayName !!} to {!! $incoming->getCharacterForUser(Auth::user()->id)->displayName !!}
                                    <br>
                                    Sent from {!! $incoming->getOtherUser(Auth::user()->id)->displayName !!}
                                </div>
                                {!! Form::open(['url' => 'links/accept', 'class' => 'd-inline-block']) !!}
                                {!! Form::hidden('id', $incoming->id) !!}
                                {!! Form::submit('Accept', ['class' => 'btn btn-success btn-sm']) !!}
                                {!! Form::close() !!}
                                {!! Form::open(['url' => 'links/delete', 'class' => 'd-inline-block']) !!}
                                {!! Form::hidden('id', $incoming->id) !!}
                                {!! Form::submit('Reject', ['class' => 'btn btn-danger btn-sm']) !!}
                                {!! Form::close() !!}
                            </li>
                        @endforeach
                    @else
                        <li class="list-group-item">
                            No incoming link requests found.
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <h3>Outgoing Requests</h3>
            <div class="card">
                <ul class="list-group list-group-flush">
                    @if ($outgoings->count())
                        @foreach ($outgoings as $outgoing)
                            <li class="list-group-item text-center">
                                <div class="mb-2">
                                    Request from {!! $outgoing->getCharacterForUser(Auth::user()->id)->displayName !!} to {!! $outgoing->getOtherCharacterForUser(Auth::user()->id)->displayName !!}
                                    <br>
                                    Sent to {!! $outgoing->getOtherUser(Auth::user()->id)->displayName !!}
                                </div>
                                {!! Form::open(['url' => 'links/delete', 'class' => 'd-inline-block']) !!}
                                {!! Form::hidden('id', $outgoing->id) !!}
                                {!! Form::submit('Cancel', ['class' => 'btn btn-danger btn-sm']) !!}
                                {!! Form::close() !!}
                            </li>
                        @endforeach
                    @else
                        <li class="list-group-item">
                            No outgoing link requests found.
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

@endsection
