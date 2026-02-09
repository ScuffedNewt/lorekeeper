@extends('admin.layout')

@section('admin-title')
    Pet Categories
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Pet Categories' => 'admin/data/pet-categories',
        ($category->id ? 'Edit' : 'Create') . ' Category' => $category->id ? 'admin/data/pet-categories/edit/' . $category->id : 'admin/data/pet-categories/create',
    ]) !!}

    <h1>{{ $category->id ? 'Edit' : 'Create' }} Category
        @if ($category->id)
            <a href="#" class="btn btn-danger float-right delete-category-button">Delete Category</a>
        @endif
    </h1>

    {!! Form::open(['url' => $category->id ? 'admin/data/pet-categories/edit/' . $category->id : 'admin/data/pet-categories/create', 'files' => true]) !!}

    <h2 class="h3">Basic Information</h2>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $category->name, ['class' => 'form-control']) !!}
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="form-check mb-2">
                {!! Form::checkbox('allow_attach', 1, $category->allow_attach, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('allow_attach', 'Allow Character Attachment', ['class' => 'form-check-label ml-2']) !!}
            </div>
            <div class="form-group row no-gutters align-items-center">
                <div class="col-md col-form-label">
                    {!! Form::label('limit', 'Hold Limit (Optional)', ['class' => 'mb-0']) !!} {!! add_help('This limit is per category and does not get overwritten by individual pet limits.') !!}
                </div>
                {!! Form::number('limit', $category->limit, ['class' => 'col-md-9 form-control px-2']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($category->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $category->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $category->id ? $category->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, this gear category will not be visible on world pages.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($category->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($category->id)
        <h2 class="h3">Preview</h2>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._entry', ['imageUrl' => $category->categoryImageUrl, 'name' => $category->displayName, 'description' => $category->parsed_description, 'visible' => $category->is_visible])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @include('js._tinymce_wysiwyg')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-category-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/pet-categories/delete') }}/{{ $category->id }}", 'Delete Category');
            });
        });
    </script>
@endsection
