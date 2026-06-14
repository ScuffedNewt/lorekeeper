    <div class="container">
        <h1>
            {!! $stat->stat->displayName !!}
        </h1>
        <div class="row">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header h2">
                        Base Stat Information
                    </div>
                    <div class="card-body">
                        <p>
                            Here you can view information about the stat at its base level.
                        </p>
                        <hr class="my-3">
                        <h4>Base Stat</h4>
                        <p>
                            {{ $stat->stat->base }}
                        </p>
                        <h4>Max Level</h4>
                        <p>
                            {{ $stat->stat->max_level ?? 'None' }}
                        </p>
                        <h4>Level Up Information</h4>
                        <p>
                            @php
                                $increment = $stat->stat->increment ?? 1;
                                $multiplier = $stat->stat->multiplier ?? 1;
                                if (($increment && $stat->base != 0) || ($multiplier && $stat->base != 0)) {
                                    // Calculate the new stat value
                                    $newStat = ($stat->stat->base + $increment) * $multiplier;

                                    // Calculate the percentage increase
                                    $percentageIncrease = (($newStat - $stat->stat->base) / $stat->stat->base) * 100 . '%';
                                } else {
                                    $newStat = $increment * $multiplier;
                                    $percentageIncrease = $newStat * 100 . '%';
                                }
                            @endphp
                            This stat increases by <b>{{ $percentageIncrease }}</b> per level up.
                            ({{ '(' . $stat->stat->base . ' + ' . $increment . ') * ' . $multiplier . ' = ' . $newStat }})
                        </p>
                        @if (count($stat->stat->limits))
                            <hr class="my-3">
                            <h4>Stat Limits</h4>
                            <p>
                                The stat applies only to the following:
                                <br />
                                {!! $stat->stat->displayLimits() !!}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header h2">
                        Current Stat Information
                    </div>
                    <div class="card-body">
                        <p>
                            Here you can view information about the stat at the current level (<b>{{ $stat->stat_level }}</b>).
                        </p>
                        <hr class="my-3">
                        <h4>Stat Value</h4>
                        <p>
                            {!! $stat->count !!}
                        </p>
                        <h4>Bonuses</h4>
                        <p class="mb-0">
                            Listed are the following equipment that apply a bonus to this stat:
                        </p>
                        <div class="text-center row">
                            @foreach ($character->getStatEquipment($stat->stat->id) as $equipment)
                                <div class="col-md-2">
                                    @if ($equipment->has_image)
                                        <img class="rounded" src="{{ $equipment->imageUrl }}" data-toggle="tooltip"
                                            title="{{ $equipment->equipment->name }}<br />+ {{ $equipment->equipment->stats()->where('stat_id', $stat->stat->id)->first()->count }}" style="max-width: 75px;" />
                                    @elseif($equipment->equipment->imageurl)
                                        <img class="rounded" src="{{ $equipment->equipment->imageUrl }}" data-toggle="tooltip"
                                            title="{{ $equipment->equipment->name }}<br />+ {{ $equipment->equipment->stats()->where('stat_id', $stat->stat->id)->first()->count }}" style="max-width: 75px;" />
                                    @else
                                        {!! $equipment->equipment->displayName !!}
                                        <small>
                                            {{ $equipment->equipment->name }}<br />+ {{ $equipment->equipment->stats()->where('stat_id', $stat->stat->id)->first()->count }}
                                        </small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr />
        <h2>Level Up</h2>
        <p>
            Here you can level up the stat. Points on the character are consumed first, then points on the user.
            <br />
            <b>Current Available Points:</b> {{ $character->level->current_points ?? 0 }} + {{ Auth::user()->level->current_points ?? 0 }} = {{ ($character->level->current_points ?? 0) + (Auth::user()->level->current_points ?? 0) }}
        </p>
        @if (Auth::check() && ($character->level->current_points || Auth::user()->level->current_points) && (Auth::user()->id == $character->user_id || Auth::user()->hasPower('edit_claymores')))
            {!! Form::open(['url' => 'character/' . $character->slug . '/stats/' . $stat->stat->id . '/level']) !!}

            <div class="text-right">
                {!! Form::submit('Level Up', ['class' => 'btn btn-primary']) !!}
            </div>

            {!! Form::close() !!}
        @endif

        @if (Auth::check() && Auth::user()->hasPower('edit_claymores'))
            <hr />
            <h2>Admin</h2>
            <p>
                Here you can edit the stat directly.
            </p>
            <div class="row">
                <div class="col-md-6">
                    <h5>Edit Base Stat Value</h5>
                    <p>This will edit the base stat value permanently.</p>
                    {!! Form::open(['url' => 'character/' . $character->slug . '/stats/' . $stat->stat->id . '/base']) !!}

                    <div class="form-group">
                        {!! Form::label('count', 'Base Stat') !!}
                        {!! Form::number('count', $stat->count, ['class' => 'form-control']) !!}
                    </div>

                    <div class="text-right">
                        {!! Form::submit('Edit Stat', ['class' => 'btn btn-primary']) !!}
                    </div>

                    {!! Form::close() !!}
                </div>
                <div class="col-md-6">
                    <h5>Edit Current Stat Value</h5>
                    <p>This will edit the current stat value temporarily.</p>
                    {!! Form::open(['url' => 'character/' . $character->slug . '/stats/' . $stat->stat->id . '/count']) !!}

                    <div class="form-group">
                        {!! Form::label('current_count', 'Current Stat') !!}
                        {!! Form::number('current_count', $stat->current_count, ['class' => 'form-control']) !!}
                    </div>

                    <div class="text-right">
                        {!! Form::submit('Edit Stat', ['class' => 'btn btn-primary']) !!}
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        @endif
    </div>
