@extends('admin.layout')

@section('admin-title')
    {{ $garden->id ? 'Edit' : 'Create' }} Garden
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Gardens' => 'admin/gardens', ($garden->id ? 'Edit' : 'Create') . ' Garden' => $garden->id ? 'admin/gardens/edit/' . $garden->id : 'admin/gardens/create']) !!}

    <h1>{{ $garden->id ? 'Edit' : 'Create' }} Garden
        @if ($garden->id)
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
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header h3">Images</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md form-group">
                    {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
                    <div>{!! Form::file('image') !!}</div>
                    <div class="text-muted">Recommended size: 200px x 200px</div>
                    @if ($garden->has_image)
                        <div class="form-check">
                            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                        </div>
                    @endif
                </div>
                <div class="col-md form-group">
                    @if ($garden->has_background_image)
                        <a href="{{ $garden->backgroundImageUrl }}" data-lightbox="entry" data-title="{{ $garden->name }}">
                            <img src="{{ $garden->backgroundImageUrl }}" class="mw-100 float-left mr-3">
                        </a>
                    @endif
                    {!! Form::label('Background Image (Optional)') !!} {!! add_help('This image will serve as the background to the garden which plots will be rendered on to.') !!}
                    <div>{!! Form::file('background_image') !!}</div>
                    <div class="text-muted">Recommended size: 16:9 ratio.</div>
                    @if ($garden->has_background_image)
                        <div class="form-check">
                            {!! Form::checkbox('remove_background_image', 1, false, ['class' => 'form-check-input']) !!}
                        </div>
                    @endif
                </div>
                <div class="col-md form-group">
                    @if ($garden->has_plot_image)
                        <a href="{{ $garden->plotImageUrl }}" data-lightbox="entry" data-title="{{ $garden->name }}">
                            <img src="{{ $garden->plotImageUrl }}" class="mw-100 float-left mr-3">
                        </a>
                    @endif
                    {!! Form::label('Default Plot Image (Optional)') !!} {!! add_help('This image is for plots that are not yet ready for planting.') !!}
                    <div>{!! Form::file('plot_image') !!}</div>
                    <div class="text-muted">Recommended size: 1:1 ratio.</div>
                    @if ($garden->has_plot_image)
                        <div class="form-check">
                            {!! Form::checkbox('remove_plot_image', 1, false, ['class' => 'form-check-input']) !!}
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
                <div class="col-md form-group">
                    {!! Form::label('Can Use Modifiers?') !!} {!! add_help('Modifiers are items that can be used on plots to change their properties.') !!}
                    {!! Form::checkbox('can_use_modifiers', 1, $garden->can_use_modifiers, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Yes', 'data-off' => 'No']) !!}
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
                    <div class="row">
                        @foreach ($garden->plots as $plot)
                            <div class="col-md-4">
                                <div class="card mb-3">
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
                                            <div class="alert alert-info">No image set.</div>
                                        @endif
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
        });
    </script>
@endsection
