@extends('admin.layout')

@section('admin-title')
    {{ $level->id ? 'Edit' : 'Create' }} Pet Level
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Pets' => 'admin/data/pets', 'Pet Levels' => 'admin/data/pets/levels', ($level->id ? 'Edit' : 'Create') . ' Level' => 'admin/data/pets/levels/' . ($level->id ? 'edit/' . $level->id : 'create')]) !!}

    <h1>
        {{ $level->id ? 'Edit' : 'Create' }} Level
        @if ($level->id)
            <a href="#" class="btn btn-outline-danger float-right delete-level-button">Delete Level</a>
        @endif
    </h1>

    {!! Form::open(['url' => $level->id ? 'admin/data/pets/levels/edit/' . $level->id : 'admin/data/pets/levels/create']) !!}

    @if (!$level->id)
        <p class="alert alert-info">
            You can add pets to a level once it has been created.
        <p>
    @endif

    <h2>Basic Information</h2>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Name') !!} {!! add_help('The name of the level, this should describe how the pet feels about the character at this level. For example, "Hates", "Indifferent", "Likes", "Loves".') !!}
                {!! Form::text('name', $level->name, ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Level') !!}
                {!! Form::number('level', $level->level, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Bonding Required') !!} {!! add_help('The amount of bonding required to reach this level.') !!}
        {!! Form::number('bonding_required', $level->bonding_required ?? 0, ['class' => 'form-control']) !!}
    </div>

    @if ($level->id)
        <h2>General Rewards</h2>
        <p>These rewards are given to the owner of a pet when they reach this level, regardless of what pet it is.</p>
        @include('widgets._loot_select', ['loots' => $level->rewards, 'showLootTables' => true, 'showRaffles' => true])

        <h2>Pet Specific Rewards</h2>
        <p>These rewards are given <i>in addition</i> to the general rewards when a pet reaches this level.</p>

        <div class="text-right mb-2">
            <a href="#" class="btn btn-primary add-pet">Add Pet</a>
        </div>
        <div class="row">
            @foreach ($level->pets as $pet)
                <div class="col-md-6">
                    <div class="card mb-2">
                        <div class="card-header">
                            <h3>{{ $pet->pet->name }}</h3>
                        </div>
                        <div class="card-body">
                            @if (count($pet->rewards))
                                <h4>Rewards</h4>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th width="70%">Reward</th>
                                            <th width="30%">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pet->rewards as $reward)
                                            <tr>
                                                @php $asset = findReward($reward->rewardable_type, $reward->rewardable_id); @endphp
                                                <td>{!! $asset->displayName !!}</td>
                                                <td>{{ $reward->quantity }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p>This pet has no specific rewards for this level.</p>
                            @endif
                            <div class="float-right">
                                <a href="{{ url('admin/data/pets/levels/edit/' . $level->id . '/pets/edit/' . $pet->id) }}" class="btn btn-primary">Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="text-right">
        {!! Form::submit($level->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._loot_select_row', ['showLootTables' => true, 'showRaffles' => true])

@endsection

@section('scripts')
    @parent
    @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
    <script>
        $(document).ready(function() {
            $('.delete-level-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/pets/levels/delete') }}/{{ $level->id }}", 'Delete Pet Level');
            });
            $('.add-pet').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/pets/levels/edit/' . $level->id . '/pets/add') }}", 'Add Pet to Level');
            });
        });
    </script>
@endsection
