@extends('admin.layout')

@section('admin-title')
    {{ $garden->id ? 'Edit' : 'Create' }} Garden
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Gardens' => 'admin/gardens', ($garden->id ? 'Edit' : 'Create') . ' Garden' => $garden->id ? 'admin/gardens/edit/' . $garden->id : 'admin/gardens/create']) !!}

    <h1>{{ $garden->id ? 'Edit' : 'Create' }} Garden
        @if ($garden->id)
            ({!! $garden->displayName !!})
            <a href="#" class="btn btn-outline-danger float-right delete-garden-button">Delete Garden</a>
        @endif
    </h1>

    {!! Form::open(['url' => $garden->id ? 'admin/gardens/edit/' . $garden->id : 'admin/gardens/create', 'files' => true]) !!}

    <div class="card mb-3">
        <div class="card-header h3">Basic Information</div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md">
                    {!! Form::label('Name') !!}
                    {!! Form::text('name', $garden->name, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group col-md">
                    {!! Form::label('Allowed Objects') !!} {!! add_help('What types of objects can be placed or used on plots in this garden? You can have more granular control on plots, but the types should be enabled here.') !!}
                    <p class="mb-0">By default, all object types are allowed.</p>
                    {!! Form::select('allowed_object_types[]', getGardenObjectTypes(), $garden->allowed_object_types, ['class' => 'form-control selectize', 'multiple', 'placeholder' => 'Select Object Types']) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header h3">Images</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md form-group d-flex flex-column">
                    @if ($garden->has_image)
                        <div class="mx-auto w-100 text-center">
                            <a href="{{ $garden->imageUrl }}" data-lightbox="entry" data-title="{{ $garden->name }}">
                                <img src="{{ $garden->imageUrl }}" class="img-fluid mw-100" />
                            </a>
                        </div>
                    @endif
                    <div>
                        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
                    </div>
                    <div class="custom-file">
                        {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
                        {!! Form::file('image', ['class' => 'custom-file-input']) !!}
                    </div>
                    <div class="text-muted">Recommended size: 100px x 100px</div>
                    @if ($garden->has_image)
                        <div class="form-check">
                            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                        </div>
                    @endif
                </div>
                <div class="col-md form-group d-flex flex-column">
                    @if ($garden->has_background_image)
                        <div class="mx-auto w-100 text-center">
                            <a href="{{ $garden->backgroundImageUrl }}" data-lightbox="entry" data-title="{{ $garden->name }}">
                                <img src="{{ $garden->backgroundImageUrl }}" class="img-fluid mw-100" />
                            </a>
                        </div>
                    @endif
                    <div>
                        {!! Form::label('Background Image (Optional)') !!} {!! add_help('This image will serve as the background to the garden which plots will be rendered on to.') !!}
                    </div>
                    <div class="custom-file">
                        {!! Form::label('background_image', 'Choose file...', ['class' => 'custom-file-label']) !!}
                        {!! Form::file('background_image', ['class' => 'custom-file-input']) !!}
                    </div>
                    <div class="text-muted">Recommended size: 16:9 ratio.</div>
                    @if ($garden->has_background_image)
                        <div class="form-check">
                            {!! Form::checkbox('remove_background_image', 1, false, ['class' => 'form-check-input']) !!}
                            {!! Form::label('remove_background_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                        </div>
                    @endif
                </div>
                <div class="col-md form-group d-flex flex-column">
                    @if ($garden->has_plot_image)
                        <div class="mx-auto w-100 text-center">
                            <a href="{{ $garden->plotImageUrl }}" data-lightbox="entry" data-title="{{ $garden->name }}">
                                <img src="{{ $garden->plotImageUrl }}" class="img-fluid mw-100" />
                            </a>
                        </div>
                    @endif
                    <div>
                        {!! Form::label('Default Plot Image (Optional)') !!} {!! add_help('This image is used for the default plot display.') !!}
                    </div>
                    <div class="custom-file">
                        {!! Form::label('plot_image', 'Choose file...', ['class' => 'custom-file-label']) !!}
                        {!! Form::file('plot_image', ['class' => 'custom-file-input']) !!}
                    </div>
                    <div class="text-muted">Recommended size: 1:1 ratio.</div>
                    @if ($garden->has_plot_image)
                        <div class="form-check">
                            {!! Form::checkbox('remove_plot_image', 1, false, ['class' => 'form-check-input']) !!}
                            {!! Form::label('remove_plot_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header h3">
            {!! Form::label('Description (Optional)') !!}
        </div>
        <div class="card-body">
            <div class="form-group">
                {!! Form::textarea('description', $garden->description, ['class' => 'form-control wysiwyg']) !!}
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header h3">
            Options
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md form-group">
                    {!! Form::label('Is Active?') !!}
                    {!! Form::checkbox('is_active', 1, $garden->id ? $garden->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Yes', 'data-off' => 'No']) !!}
                </div>
                <div class="col-md form-group">
                    {!! Form::label('Can Perish?') !!} {!! add_help('Can the plot contents perish if not maintained?') !!}
                    {!! Form::checkbox('can_perish', 1, $garden->can_perish, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Yes', 'data-off' => 'No']) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($garden->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($garden->id)
        @include('widgets._add_limits', ['object' => $garden])

        <div class="card mt-3">
            <div class="card-header h3">Plots</div>
            <div class="card-body">
                <p>Plots are the individual areas within the garden that users can plant in / place items in. You can manage the plots for this garden below.</p>
                <div class="btn btn-primary create-plot float-right">Create New Plot</div>
                <hr />
                @if (!$garden->plots->count())
                    <p>No plots found.</p>
                @else
                    <div class="row no-gutters justify-content-center text-center">
                        @foreach ($garden->plots as $plot)
                            <div class="col-md-4 justify-content-center text-center mb-3">
                                <div class="card h-100 mx-2">
                                    <div class="card-body">
                                        @if ($plot->has_image)
                                            <a href="{{ $plot->imageUrl }}" data-lightbox="entry" data-title="{{ $plot->name }}">
                                                <img src="{{ $plot->imageUrl }}" class="mw-100 mb-3">
                                            </a>
                                        @elseif ($garden->has_plot_image)
                                            <a href="{{ $garden->plotImageUrl }}" data-lightbox="entry" data-title="{{ $garden->name }}">
                                                <img src="{{ $garden->plotImageUrl }}" class="mw-100 mb-3">
                                            </a>
                                        @else
                                            <div class="alert alert-info">No plot image set.</div>
                                        @endif
                                        @include('widgets._limits', [
                                            'object' => $plot,
                                            'compact' => true,
                                            'hideUnlock' => true,
                                        ])
                                        <div class="text-center">
                                            <a class="btn btn-primary" href="{{ url('admin/gardens/edit/' . $garden->id . '/plots/edit/' . $plot->id) }}">Edit Plot</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection
@section('scripts')
    @include('js._tinymce_wysiwyg')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-garden-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/gardens/delete') }}/{{ $garden->id }}", 'Delete Garden');
            });

            @if ($garden->id)
                $('.create-plot').on('click', function() {
                    loadModal("{{ url('admin/gardens/edit/' . $garden->id . '/plots/create') }}", 'Create Plot');
                });
            @endif

            $('.selectize').selectize();
        });
    </script>
@endsection
