@extends('admin.layout')

@section('admin-title')
    Weapons
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Weapons' => 'admin/weapons', ($weapon->id ? 'Edit' : 'Create') . ' Weapon' => $weapon->id ? 'admin/weapons/edit/' . $weapon->id : 'admin/weapons/create']) !!}

    <h1>{{ $weapon->id ? 'Edit' : 'Create' }} Weapon
        @if ($weapon->id)
            <a href="#" class="btn btn-outline-danger float-right delete-weapon-button">Delete Weapon</a>
        @endif
    </h1>

    {!! Form::open(['url' => $weapon->id ? 'admin/weapons/edit/' . $weapon->id : 'admin/weapons/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $weapon->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($weapon->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Weapon Category (Optional)') !!}
                {!! Form::select('weapon_category_id', $categories, $weapon->weapon_category_id, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Weapon Parent (Optional)') !!}
                @if ($weapon->id)
                    <div class="text-muted">You can set upgrade costs when the parent weapon is set.</div>
                @endif
                {!! Form::select('parent_id', $weapons, $weapon->parent_id, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $weapon->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md form-group">
            {!! Form::checkbox('allow_transfer', 1, $weapon->id ? $weapon->allow_transfer : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('allow_transfer', 'Allow User → User Transfer', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to transfer this weapon to other users. Non-account-bound weapons can be account-bound when granted to users directly.') !!}
        </div>
        <div class="col-md form-group">
            {!! Form::checkbox('is_visible', 1, $weapon->id ? $weapon->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, this gear category will not be visible on world pages.') !!}
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($weapon->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($weapon->id)
        @if ($stats->count())
            {!! Form::open(['url' => 'admin/weapons/stats/' . $weapon->id]) !!}
            <h3>Stats {!! add_help('Leave empty to have no effect on stat.') !!}</h3>

            @foreach ($stats as $stat)
                @php
                    if ($weapon->stats->where('stat_id', $stat->id)->first()) {
                        $base = $weapon->stats->where('stat_id', $stat->id)->first()->count;
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
            'object' => $weapon,
            'info' => 'Limits are used to upgrade weapons.',
            'showUnlocked' => false,
            'customHeader' => 'Weapon Upgrade Limits',
        ])

        @include('widgets._add_typing', ['object' => $weapon, 'info' => 'Typings are used to determine effectiveness in battles.'])

        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._claymore_entry', [
                    'item' => $weapon,
                    'imageUrl' => $weapon->imageUrl,
                    'name' => $weapon->displayName,
                    'description' => $weapon->description,
                    'searchUrl' => $weapon->searchUrl,
                    'visible' => $weapon->is_visible,
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

            $('.delete-weapon-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/weapons/delete') }}/{{ $weapon->id }}", 'Delete Weapon');
            });
        });
    </script>
@endsection
