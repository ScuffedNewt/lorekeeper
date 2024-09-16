@extends('admin.layout')

@section('admin-title')
    Edit Pet Level Rewards
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Pets' => 'admin/data/pets',
        'Pet Levels' => 'admin/data/pets/levels',
        'Edit Level' => 'admin/data/pets/levels/edit/' . $level->id,
        'Edit Rewards' => 'admin/data/pets/levels/edit/' . $level->id . '/pets/edit' . $petLevel->id,
    ]) !!}

    <h1>
        Edit {!! $petLevel->pet->displayName !!} Level Rewards
    </h1>

    <div class="text-right mb-2">
        <a href="{{ url('admin/data/pets/levels/edit/' . $level->id) }}" class="btn btn-info"><i class="fas fa-arrow-left"></i> Back to Level</a>
    </div>

    {!! Form::open(['url' => 'admin/data/pets/levels/edit/' . $level->id . '/pets/edit/' . $petLevel->id]) !!}

    @include('widgets._loot_select', ['loots' => $petLevel->rewards, 'showLootTables' => true, 'showRaffles' => true])

    <div class="text-right">
        {!! Form::submit($level->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._loot_select_row', ['showLootTables' => true, 'showRaffles' => true])
@endsection

@section('scripts')
    @parent
    @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
@endsection
