@extends('admin.layout')

@section('admin-title')
    Maps
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Maps' => 'admin/maps', ($map->id ? 'Edit' : 'Create') . ' Map' => $map->id ? 'admin/maps/edit/' . $map->id : 'admin/maps/create']) !!}

    <h1>{{ $map->id ? 'Edit' : 'Create' }} Map
        @if ($map->id)
            <a href="#" class="btn btn-outline-danger float-right delete-map-button">Delete Map</a>
        @endif
    </h1>

    {!! Form::open(['url' => $map->id ? 'admin/maps/edit/' . $map->id : 'admin/maps/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $map->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Image') !!}
        <div>{!! Form::file('image') !!}</div>
        @if ($map->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $map->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="col-md form-group">
        {!! Form::checkbox('is_active', 1, $map->id ? $map->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit($map->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($map->id)
        <h1>Map Locations</h1>
        <p class="text-muted">These are the locations that are currently on this map.</p>
        <p>To get image co-ordinates for locations, use a site like <a href="https://www.image-map.net/" target="_blank">Image Map</a></p>
        <hr />
        <div class="text-right my-3">
            <a class="btn btn-primary create-location"><i class="fas fa-plus"></i> Create New Location</a>
        </div>
        @if (!count($map->locations))
            <p>No locations found.</p>
        @else
            <table class="table table-sm table-responsive-md">
                <thead>
                    <th>Name</th>
                    <th>Image Co-ordinates</th>
                    <th></th>
                    <th></th>
                </thead>
                <tbody>
                    @foreach ($map->locations as $location)
                        <tr>

                            <td>{{ $location->name }}</td>
                            <td>{{ $location->cords }}</td>
                            <td>
                                <a href="#" class="btn btn-primary btn-sm edit-location" data-id="{{ $location->id }}"><i class="fas fa-edit"></i></a>
                            </td>
                            <td>
                                <a href="#" class="btn btn-danger btn-sm delete-location" data-id="{{ $location->id }}"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <hr />
        <h1>Preview</h1>
        <p class="text-muted">This is a preview of the map.</p>
        <div class="container">
            {!! $map->display !!}
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();

            $('.delete-map-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/maps/delete') }}/{{ $map->id }}", 'Delete Map');
            });

            $('.create-location').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/maps/locations/create') }}/{{ $map->id }}", 'Create Location');
            });

            $('.edit-location').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/maps/locations/edit') }}/" + $(this).data('id'), 'Edit Location');
            });

            $('.delete-location').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/maps/locations/delete') }}/" + $(this).data('id'), 'Delete Location');
            });
        });
    </script>
@endsection
