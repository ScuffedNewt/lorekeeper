@extends('home.layout')

@section('home-title')
    Stat Information
@endsection

@section('home-content')
    {!! breadcrumbs(['Stats' => 'user-stats']) !!}

    <h1>
        Your Stat Information
    </h1>

    @if (config('lorekeeper.claymores_and_companions.visibility_settings.user_levels'))
        @include('widgets._level_info', ['level' => $user->level])
    @endif

    @if (config('lorekeeper.claymores_and_companions.visibility_settings.character_stats'))
        <div class="card mb-3">
            <div class="card-header h2">
                Stat Information
                <span class="badge badge-dark text-white mx-1 float-right" data-toggle="tooltip" title="Current Stat Points">
                    Available Stat Points: {{ $user->level->current_points }}
                </span>
            </div>
            <div class="card-body">
                <p>Stat points can be spent on your character's stats. These stats are used to determine your character's strength in battle.</p>
                <p>Each stat point spent on a stat will increase that stat by a predetermined sum. Each stat has a base value, which is the value of the stat without any stat points spent on it. The base value of a stat can be increased by equipping
                    items
                    that increase that stat.</p>
                <p>Each stat has a maximum value, which is the maximum value that stat can be leveled to.</p>
                <p><a href="{{ url('characters') }}" class="text-info">View Characters</a></p>
            </div>
        </div>

        <div class="text-right mb-4">
            <a href="{{ url($user->url . '/stats') }}">View logs...</a>
        </div>
    @endif
@endsection
