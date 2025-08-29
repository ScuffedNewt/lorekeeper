@extends('admin.layout')

@section('admin-title')
    {{ $runbook->id ? 'Edit' : 'Create' }} Runbook
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Runbooks' => 'admin/runbooks', ($runbook->id ? 'Edit' : 'Create') . ' Runbook' => $runbook->id ? 'admin/runbooks/edit/' . $runbook->id : 'admin/runbooks/create']) !!}

    <h1>{{ $runbook->id ? 'Edit' : 'Create' }} Runbook
        @if ($runbook->id && !config('lorekeeper.text_runbooks.' . $runbook->key))
            <a href="#" class="btn btn-danger float-right delete-runbook-button">Delete Runbook</a>
        @endif
        @if ($runbook->id)
            <a href="{{ $runbook->url }}" class="btn btn-info float-right mr-md-2">View Runbook</a>
        @endif
    </h1>

    {!! Form::open(['url' => $runbook->id ? 'admin/runbooks/edit/' . $runbook->id : 'admin/runbooks/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    @if ($runbook->id && $runbook->type == 'Subsection')
        <div class="alert alert-info">
            This runbook can be referenced by other runbooks and have its contents be rendered inside the parent runbook.
            <br />
            You can reference this runbook by using the following syntax in the parent runbook's content:
            <br />
            <code>[runbook:{{ $runbook->id }}]</code>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('Title') !!}
            {!! Form::text('title', $runbook->title, ['class' => 'form-control']) !!}
        </div>

        <div class="col-md-6 form-group">
            {!! Form::label('type') !!} {!! add_help('This determines the type of runbook being created, and where it appears. Subsection runbooks can be referenced on other runbooks.') !!}
            {!! Form::select('type', array_combine(config('lorekeeper.runbooks.types'), config('lorekeeper.runbooks.types')), $runbook->type, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Runbook Content') !!}
        {!! Form::textarea('text', $runbook->text, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_public', 1, $runbook->id ? $runbook->is_public : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_public', 'Is Public', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, users who are not staff will not be able to view the runbook even if they have the link to it.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($runbook->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.delete-runbook-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/runbooks/delete') }}/{{ $runbook->id }}", 'Delete Runbook');
            });
            $('.subrunbook').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/runbooks/create/' . $runbook->id) }}", 'Create Sub-Runbook');
            });
        });
    </script>
@endsection
