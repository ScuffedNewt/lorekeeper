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
            <div class="form-group">
                {!! Form::label('name', 'Plot Name') !!}
                {!! Form::text('name', $plot->name, ['class' => 'form-control']) !!}
            </div>
            <div class="row">
                @if ($plot->has_image)
                    <div class="form-group col-md-2">
                        <img src="{{ $plot->image_url }}" class="img-fluid" />
                    </div>
                @endif
                <div class="form-group {{ $plot->has_image ? 'col-md-10' : 'col-md-12' }}">
                    {!! Form::label('Plot Image (Optional)') !!} {!! add_help('This image will be used for the plot display. If not set, the default garden plot image will be used.') !!}
                    <div class="custom-file">
                        {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
                        {!! Form::file('image', ['class' => 'custom-file-input']) !!}
                    </div>
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
                @php
                    // get only the types on the garden from getGardenObjectTypes()
                    $availableTypes = array_intersect_key(getGardenObjectTypes(), $plot->garden->allowed_object_types ? array_flip($plot->garden->allowed_object_types) : getGardenObjectTypes());
                @endphp
                <div class="form-group col-md">
                    {!! Form::label('Allowed Objects') !!} {!! add_help('What types of objects can be placed or used on plots in this garden? You can have more granular control on plots, but the types should be enabled here.') !!}
                    <p>By default, all object types allowed in the garden are allowed here.</p>
                    <div class="mb-2"><strong>Garden Allowed Types:</strong> {{ implode(', ', array_intersect_key(getGardenObjectTypes(), array_flip($plot->data['allowed_object_types'] ?? []))) ?: 'All' }}</div>
                    {!! Form::select('allowed_object_types[]', $availableTypes, $plot->data['allowed_object_types'] ?? null, ['class' => 'form-control selectize', 'multiple', 'placeholder' => 'Select Object Types']) !!}
                </div>
                <div class="col-md form-group">
                    {!! Form::label('Can Perish?') !!} {!! add_help('Can the plot contents perish if not maintained?') !!}
                    {!! Form::checkbox('can_perish', 1, $plot->data['can_perish'] ?? null, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Yes', 'data-off' => 'No']) !!}
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
        <b>Note</b>: plot unlocks are handled differently than other limits. The limits are not automatically unlocked, the user must click the "Unlock" button on the plot to unlock it, which will check if they meet the requirements and then unlock it for them if they do. This is to allow users to choose when to unlock the plot rather than it being forced on them when they meet the requirements.
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
            $('.selectize').selectize();
            $('.delete-plot-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/gardens/edit/' . $garden->id . '/plots/delete') }}/{{ $plot->id }}", 'Delete Garden Plot');
            });
        });
    </script>
@endsection
