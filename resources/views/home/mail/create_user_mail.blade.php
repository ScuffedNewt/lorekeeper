@extends('home.layout')

@section('home-title')
    Create Direct Message
@endsection

@section('home-content')
    {!! breadcrumbs(['Inbox' => 'inbox', 'Create Message' => 'inbox/new']) !!}

    <h1>Create Direct Message</h1>

    {!! Form::open(['url' => 'inbox/new']) !!}

    <div class="form-group">
        {!! Form::label('recipient_id', 'Recipient Username') !!}
        {!! Form::select('recipient_id', $users, Request::get('recipient_id'), ['id' => 'usernameList', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('subject', 'Message Subject') !!}
        {!! Form::text('subject', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('message', 'Message') !!}
        {!! Form::textarea('message', null, ['class' => 'form-control wysiwyg']) !!}
    </div>

    {{ Form::hidden('parent_id', null) }}

    <div class="text-right">
        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <script>
        $(document).ready(function() {
            $('#usernameList').selectize();
        });
    </script>
@endsection
