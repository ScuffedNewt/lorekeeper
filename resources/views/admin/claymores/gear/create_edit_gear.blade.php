@extends('admin.layout')

@section('admin-title')
    Gear
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Gear' => 'admin/gear', ($gear->id ? 'Edit' : 'Create') . ' Gear' => $gear->id ? 'admin/gear/edit/' . $gear->id : 'admin/gear/create']) !!}

    <h1>{{ $gear->id ? 'Edit' : 'Create' }} Gear
        @if ($gear->id)
            <a href="#" class="btn btn-outline-danger float-right delete-gear-button">Delete Gear</a>
        @endif
    </h1>

    {!! Form::open(['url' => $gear->id ? 'admin/gear/edit/' . $gear->id : 'admin/gear/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $gear->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($gear->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Gear Category (Optional)') !!}
                {!! Form::select('gear_category_id', $categories, $gear->gear_category_id, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Gear Parent (Optional)') !!}
                @if ($gear->id)
                    <div class="text-muted">You can set upgrade costs when the parent gear is set.</div>
                @endif
                {!! Form::select('parent_id', $gears, $gear->parent_id, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $gear->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md form-group">
            {!! Form::checkbox('allow_transfer', 1, $gear->id ? $gear->allow_transfer : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('allow_transfer', 'Allow User → User Transfer', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to transfer this gear to other users. Non-account-bound gears can be account-bound when granted to users directly.') !!}
        </div>
        <div class="col-md form-group">
            {!! Form::checkbox('is_visible', 1, $gear->id ? $gear->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, this gear category will not be visible on world pages.') !!}
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($gear->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($gear->id)
        @if ($stats->count())
            {!! Form::open(['url' => 'admin/weapons/stats/' . $gear->id]) !!}
            <h3>Stats {!! add_help('Leave empty to have no effect on stat.') !!}</h3>

            @foreach ($stats as $stat)
                @php
                    if ($gear->stats->where('stat_id', $stat->id)->first()) {
                        $base = $gear->stats->where('stat_id', $stat->id)->first()->count;
                    } else {
                        $base = null;
                    }
                @endphp
                <div class="form-group">
                    {!! Form::label($stat->name) !!}
                    {!! Form::number('stats[' . $stat->id . ']', $base, ['class' => 'form-control']) !!}
                </div>
            @endforeach

            <div class="text-right">
                {!! Form::submit('Edit Stats', ['class' => 'btn btn-primary']) !!}
            </div>

            {!! Form::close() !!}
        @else
            <div class="alert alert-warning mt-3">
                <strong>Warning:</strong> No stats have been created yet. You can create stats <a href="{{ url('admin/stats/create') }}">here</a>.
            </div>
        @endif

        @include('widgets._add_limits', [
            'object' => $gear,
            'info' => 'Limits are used to upgrade gears.',
            'showUnlocked' => false,
            'customHeader' => 'Gear Upgrade Limits',
        ])

        @include('widgets._add_typing', ['object' => $gear, 'info' => 'Typings are used to determine effectiveness in battles.'])

        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._claymore_entry', [
                    'item' => $gear,
                    'imageUrl' => $gear->imageUrl,
                    'name' => $gear->displayName,
                    'description' => $gear->description,
                    'searchUrl' => $gear->searchUrl,
                    'visible' => $gear->is_visible,
                ])
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();

            $('.delete-gear-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/gear/delete') }}/{{ $gear->id }}", 'Delete Gear');
            });
        });
    </script>
@endsection
