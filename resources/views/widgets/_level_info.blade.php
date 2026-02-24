<div class="card mb-3">
    <div class="card-header h2">
        Level Information
        <span class="badge badge-{{ $level->nextLevel ? 'dark' : 'success' }} text-white mx-1 float-right" data-toggle="tooltip" title="{{ $level->level?->name }}">
            {{ $level->nextLevel ? 'Current: ' . $level->level->name : 'Max Level' }}
        </span>
    </div>
    <div class="card-body">
        <div class="container text-center">
            @if ($level->nextLevel)
                <p><b>Next Level:</b> {{ $level->nextLevel->name }}</p>
                {{ $level->experience?->quantity ?? 0 }}/{{ $level->nextLevel->exp_required }}
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active progress-bar-animated" role="progressbar" aria-valuenow="{{ $level->experience?->quantity ?? 0 }}" aria-valuemin="0" aria-valuemax="{{ $level->nextLevel->exp_required }}"
                        style="width:{{ $level->progressBarWidth }}%">
                        {{ $level->experience?->quantity ?? 0 }}/{{ $level->nextLevel->exp_required }}
                    </div>
                </div>
                @if ($level->experience?->quantity >= $level->nextLevel->exp_required && Auth::check() && ($level->user ?? Auth::user()->id == $level->character?->user_id))
                    <div class="text-center m-1">
                        <b>
                            <p>You have enough EXP to advance to the next level!</p>
                        </b>
                    </div>
                    {!! Form::open(['url' => $level->user ? '/user-stats/level' : $level->character->url . '/stats/level']) !!}

                    {!! Form::submit('Level Up!', ['class' => 'btn btn-success']) !!}

                    {!! Form::close() !!}
                @endif
            @else
                {{ $level->experience?->quantity ?? 0 }} Exp (Max Level)
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="{{ $level->experience?->quantity ?? 0 }}" aria-valuemin="0" aria-valuemax="{{ $level->experience?->quantity ?? 0 }}" style="width:100%">
                        {{ $level->experience?->quantity ?? 0 }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
