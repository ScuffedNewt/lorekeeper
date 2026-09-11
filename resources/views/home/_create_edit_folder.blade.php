@if ($folder->id)
    <div class="text-right">
        <div class="btn btn-danger delete">
            Delete Folder?
        </div>
    </div>
    <div class="collapse collapse-delete">
        {!! Form::open(['url' => 'characters/folder/delete/' . $folder->id]) !!}

        <p>You are about to delete the folder <strong>{{ $folder->name }}</strong>. This is not reversible.</p>
        <p>Are you sure you want to delete <strong>{{ $folder->name }}</strong>?</p>

        <div class="text-right">
            {!! Form::submit('Delete Folder', ['class' => 'btn btn-danger']) !!}
        </div>

        {!! Form::close() !!}
    </div>
@endif

{!! Form::open(['url' => 'characters/folder/' . ($folder->id ? 'edit/' . $folder->id : 'create')]) !!}

<div class="form-group">
    {!! Form::label('name', 'Name') !!}
    {!! Form::text('name', $folder->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('description', 'Description') !!}
    {!! Form::text('description', $folder->description, ['class' => 'form-control wysiwyg']) !!}
</div>

{!! Form::submit(($folder->id ? 'Edit' : 'Create') . ' Folder', ['class' => 'btn btn-primary']) !!}

{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('.delete').click(function() {
            $('.collapse-delete').collapse('toggle');
        });
    });
</script>
