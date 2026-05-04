@extends('admin.layout')

@section('admin-title')
    Create Mod Mail
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Mod Mail' => 'admin/mail', 'Create Mod Mail' => 'admin/mail/create']) !!}

    <h1>Create Mod Mail</h1>

    {!! Form::open(['url' => 'admin/mail/create']) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('user_id', 'Username') !!}
        {!! Form::select('user_id', $users, null, ['id' => 'usernameList', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('subject', 'Message Subject') !!}
        {!! Form::text('subject', null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('message', 'Message') !!}
        {!! Form::textarea('message', null, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <h3>Additional Data</h3>

    <div class="form-group">
        {!! Form::checkbox('issue_strike', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'strike']) !!}
        {!! Form::label('issue_strike', 'Issue Strike?', ['class' => 'form-check-label ml-3']) !!}
    </div>

    <div class="form-group hide" id="strike-input">
        {!! Form::label('strike_count', 'Strike Count') !!} {!! add_help('If multiple strikes need to be issues set the value here.') !!}
        {!! Form::number('strike_count', 1, ['class' => 'form-control', 'min' => '1']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <script>
        $(document).ready(function() {
            $('#usernameList').selectize();
            $('#strike').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#strike-input').removeClass('hide');
                } else {
                    $('#strike-input').addClass('hide');
                }
            });
        });
    </script>
@endsection
