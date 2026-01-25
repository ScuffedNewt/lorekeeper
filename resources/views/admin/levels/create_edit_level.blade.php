@extends('admin.layout')

@section('admin-title')
    Levels
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        ucfirst($type) . ' Levels' => 'admin/levels/' . $type,
        ($level->id ? 'Edit' : 'Create ') . ucfirst($type) . ' Level' => $level->id ? 'admin/levels/' . $type . '/edit/' . $level->id : 'admin/levels/' . $type . '/create',
    ]) !!}

    <h1>{{ $level->id ? 'Edit' : 'Create' }} {{ ucfirst($type) }} Level
        @if ($level->id)
            <a href="#" class="btn btn-outline-danger float-right delete-level-button">Delete Level</a>
        @endif
    </h1>

    {!! Form::open(['url' => $level->id ? 'admin/levels/' . $type . '/edit/' . $level->id : 'admin/levels/' . $type . '/create']) !!}

    <h3>Basic Information</h3>
    <p>All {{ $type }}s start at level one</p>
    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Level (e.g 2)') !!}
                {!! Form::number('level', $level->level, ['class' => 'form-control', 'min' => 2]) !!}
            </div>
        </div>
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('EXP Required') !!}
                {!! Form::number('exp_required', $level->exp_required, ['class' => 'form-control', 'min' => 1]) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Description') !!}
        {!! Form::text('description', $level->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    {{-- blade-formatter-disable --}}
    @include('widgets._add_rewards', [
        'object' => $level,
        'useForm' => false,
        'showRaffles' => true,
        'showLootTables' => true,
        'showRecipient' => true,
        'info' => $type == 'character' ?
            'Character rewards are currently set to be awarded to ' . (config('lorekeeper.extensions.character_reward_expansion.default_recipient') ? 'the character' : 'the user') . '.'
            : null,
        'type' => 'Reward',
    ])
    {{-- blade-formatter-enable --}}

    <div class="text-right">
        {!! Form::submit($level->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <hr />

    @if ($level->id)
        @include('widgets._add_limits', [
            'object' => $level,
            'showUnlocked' => false,
        ])
    @else
        <h3>Limits</h3>
        <div class="alert alert-info">
            <strong>Save the level first</strong> to add limits.
        </div>
    @endif

    @include('widgets._loot_select_row', ['showLootTables' => true, 'showRaffles' => true])
@endsection

@section('scripts')
    @include('js._tinymce_wysiwyg')
    @parent
    <script>
        $(document).ready(function() {

            $('.delete-level-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/levels/') }}/{{ $type }}/delete/{{ $level->id }}", 'Delete Level');
            });
        });
    </script>
@endsection
