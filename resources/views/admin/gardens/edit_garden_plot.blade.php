@extends('admin.layout')

@section('admin-title')
    Edit Garden Plot
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Gardens' => 'admin/gardens',
        'Edit Garden' => 'admin/gardens/edit/' . $garden->id,
        'Edit Plot' => 'admin/gardens/edit/' . $plot->id . '/plots/edit/' . $plot->id,
    ]) !!}

    <h1>
        Edit Garden Plot
        @if ($plot->id)
            <a href="#" class="btn btn-outline-danger float-right delete-plot-button">Delete Garden Plot</a>
        @endif
    </h1>

    {!! Form::open(['url' => 'admin/gardens/edit/' . $garden->id . '/plots/edit/' . $plot->id, 'files' => true]) !!}

    <div class="card mb-3">
        <div class="card-header h3">Basic Information</div>
        <div class="card-body">
            <div class="row">
                @if ($plot->has_image)
                    <div class="form-group col-md-2">
                        <img src="{{ $plot->image_url }}" class="img-fluid" />
                    </div>
                @endif
                <div class="form-group {{ $plot->has_image ? 'col-md-10' : 'col-md-12' }}">
                    {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
                    <div>{!! Form::file('image') !!}</div>
                    <div class="text-muted">Recommended size: 200px x 200px</div>
                    @if ($plot->has_image)
                        <div class="form-check">
                            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                        </div>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md">
                    {!! Form::checkbox('can_use_modifiers', 1, $plot->can_use_modifiers, ['class' => 'form-control', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('can_use_modifiers', 'Can Use Modifiers?', ['class' => 'ml-3 form-check-label']) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <hr />

    <div class="alert alert-info">
        <b>Note</b>: plot unlocks are handled differently than other limits. The limits are not automatically unlocked.
    </div>
    @include('widgets._add_limits', [
        'object' => $plot,
        'extra_options' => false,
        'extra_options_values' => [
            'is_unlocked' => 'yes',
            'is_auto_unlocked' => 'no',
        ],
    ])
@endsection
@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-plot-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/gardens/edit/' . $garden->id . '/plots/delete') }}/{{ $plot->id }}", 'Delete Garden Plot');
            });
        });
    </script>
@endsection
