@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Level
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Stats' => $user->url . '/stats']) !!}

    <h1>
        {!! $user->displayName !!}'s Stats
    </h1>

    @if (config('lorekeeper.claymores_and_companions.visibility_settings.user_levels'))
        @include('widgets._level_info', ['level' => $user->level])

        @if (Auth::check() && Auth::user()->id == $user->id)
            <div class="text-right">
                <a href="{{ url('user-stats') }}">
                    <div class="btn btn-primary mr-0">
                        Go to Personal Stat Page
                    </div>
                </a>
            </div>
        @endif

        <h3>Latest EXP Activity</h3>
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Sender</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Recipient</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Quantity</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="logs-table-cell">Log</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Date</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($exps as $exp)
                    <div class="logs-table-row">
                        @include('user._exp_log_row', ['exp' => $exp, 'owner' => $user])
                    </div>
                @endforeach
            </div>
        </div>
        <div class="text-right">
            <a href="{{ url($user->url . '/stats/logs/experience') }}">View all...</a>
        </div>

        <h3>Latest Level-Up Activity</h3>
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell"></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Old Level</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">New Level</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="logs-table-cell">Date</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($levels as $level)
                    <div class="logs-table-row">
                        @include('user._level_log_row', ['exp' => $level, 'owner' => $user])
                    </div>
                @endforeach
            </div>
        </div>
        <div class="text-right">
            <a href="{{ url($user->url . '/stats/logs/level') }}">View all...</a>
        </div>
    @endif

    @if (config('lorekeeper.claymores_and_companions.visibility_settings.character_stats'))
        <h3>Latest Stat Activity</h3>
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Sender</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Recipient</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Quantity</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="logs-table-cell">Log</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Date</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($stats as $stat)
                    <div class="logs-table-row">
                        @include('user._stat_log_row', ['exp' => $stat, 'owner' => $user])
                    </div>
                @endforeach
            </div>
        </div>
        <div class="text-right">
            <a href="{{ url($user->url . '/stats/logs/stat-points') }}">View all...</a>
        </div>
    @endif
@endsection
