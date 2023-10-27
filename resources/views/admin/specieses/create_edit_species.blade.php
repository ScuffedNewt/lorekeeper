@extends('admin.layout')

@section('admin-title')
    Species
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Species' => 'admin/data/species', ($species->id ? 'Edit' : 'Create') . ' Species' => $species->id ? 'admin/data/species/edit/' . $species->id : 'admin/data/species/create']) !!}

    <h1>{{ $species->id ? 'Edit' : 'Create' }} Species
        @if ($species->id)
            <a href="#" class="btn btn-danger float-right delete-species-button">Delete Species</a>
        @endif
    </h1>

    {!! Form::open(['url' => $species->id ? 'admin/data/species/edit/' . $species->id : 'admin/data/species/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $species->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Sub Masterlist (Optional)') !!} {!! add_help('This puts it onto a sub masterlist.') !!}
        {!! Form::select('masterlist_sub_id', $sublists, $species->masterlist_sub_id, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($species->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $species->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Chance to inherit') !!} {!! add_help('For pairings, how likely this species is to be passed on in percent. Must be a number between 1-100. Defaults to 50. Does not guarantee a species is inherited 100% of the time as the chances for both species are added!') !!}
        {!! Form::number('inherit_chance', $species->inherit_chance ?? 50, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $species->id ? $species->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the species will not be visible in the species list or available for selection in search and design updates. Permissioned staff will still be able to add them to characters, however.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($species->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($species->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._species_entry', ['species' => $species])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-species-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/species/delete') }}/{{ $species->id }}", 'Delete Species');
            });
        });
    </script>
@endsection
