@extends('home.layout')

@section('home-title')
    Message (#{{ $mail->id }})
@endsection

@section('home-content')

    {!! breadcrumbs(['Inbox' => 'inbox', $mail->displayName . ' from ' . $mail->sender->displayName => $mail->viewUrl]) !!}

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-6">
                    <h3>Mail #{{ $mail->id }} - {!! $mail->displayName !!}</h3>
                </div>
                <div class="col-6 text-right">
                    <h5>Sent {!! pretty_date($mail->created_at) !!}</h5>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="card-text">
                {!! $mail->message !!}
            </div>
        </div>
    </div>
    @if ($mail->parent && $mail->children)
        <div class="card mb-3">
            <div class="btn card-header text-right" data-toggle="collapse" href="#collapseExample" role="button">
                <h5>Mail #{{ $mail->id }} History</h5>
            </div>
            <div class="collapse card-body pb-0" id="collapseExample">
                @if ($mail->parent)
                    <p><strong>Previous Message:</strong> {!! $mail->parent->displayName !!} ({{ Illuminate\Support\Str::limit($mail->parent->message, 25, $end = '...') }})</p>
                @endif
                @if ($mail->parent && $mail->children)
                    <hr>
                @endif
                @if ($mail->children)
                    @foreach ($mail->children as $child)
                        <p><strong>Replies:</strong> {!! $child->displayName !!} ({{ Illuminate\Support\Str::limit($child->message, 50, $end = '...') }})</p>
                    @endforeach
                @endif
            </div>
        </div>
    @endif

    <br>

    @if (Auth::user()->id != $mail->sender_id)
        {!! Form::open(['url' => 'inbox/new']) !!}

        <div class="form-group">
            @if ($mail->children)
                {!! Form::label('message', 'Send New Reply') !!}
            @else
                {!! Form::label('message', 'Send Reply') !!}
            @endif
            {!! Form::textarea('message', null, ['class' => 'form-control wysiwyg']) !!}
        </div>

        {{ Form::hidden('parent_id', $mail->id) }}
        {{ Form::hidden('subject', $mail->subject) }}
        {{ Form::hidden('recipient_id', $mail->sender_id) }}

        <div class="text-right">
            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
        </div>

        {!! Form::close() !!}
    @endif

@endsection
