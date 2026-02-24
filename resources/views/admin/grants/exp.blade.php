@extends('admin.layout')

@section('admin-title')
    Grant EXP
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Grant Experience' => 'admin/grants/experience']) !!}

    <h1>Grant Experience</h1>

    {!! Form::open(['url' => 'admin/grants/experience']) !!}

    <h3>Basic Information</h3>
    <div class="form-group">
        {!! Form::label('names[]', 'Username(s) / Slug(s)') !!} {!! add_help('You can select up to 10 users at once.') !!}
        {!! Form::select('names[]', $options, null, ['id' => 'usernameList', 'class' => 'form-control', 'multiple']) !!}
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('Experience') !!} {!! add_help('Must select an experience and Quantity must be at least 1.') !!}
            {!! Form::select('experience_id', $experiences, null, ['class' => 'form-control', 'placeholder' => 'Select Experience']) !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::label('Quantity') !!}
            {!! Form::number('quantity', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
        </div>
    </div>

    <h3>Additional Data</h3>

    <div class="form-group">
        {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the logs and in the inventory description.') !!}
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
