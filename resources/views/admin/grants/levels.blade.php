@extends('admin.layout')

@section('admin-title')
    Grant Levels
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Grant Levels' => 'admin/grants/levels']) !!}

    <h1>Grant Levels</h1>

    {!! Form::open(['url' => 'admin/grants/levels']) !!}

    <h3>Basic Information</h3>
    <div class="form-group">
        {!! Form::label('names[]', 'Username(s) / Slug(s)') !!} {!! add_help('You can select up to 10 users or characters at once.') !!}
        {!! Form::select('names[]', $options, null, ['id' => 'usernameList', 'class' => 'form-control', 'multiple']) !!}
    </div>

    <p>Grant whole level steps without spending EXP. Each gained level awards its configured rewards.
        Negative quantities remove levels without reclaiming past rewards. The entire grant fails if any recipient
        would move beyond their first or last configured level.</p>
    <div class="form-group">
        {!! Form::label('quantity', 'Levels') !!}
        {!! Form::number('quantity', 1, ['class' => 'form-control', 'required', 'step' => 1]) !!}
    </div>
    <h3>Additional Data</h3>

    <div class="form-group">
        {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the admin log.') !!}
        {!! Form::text('data', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
    </div>

    <div class="text-right">
        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <script>
        $(document).ready(function() {
            $('#usernameList').selectize({
                maxItems: 10
            });
        });
    </script>
@endsection
