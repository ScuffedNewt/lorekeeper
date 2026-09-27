@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Level Logs
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Level' => $user->url . '/level', 'Logs' => $user->url . '/stats/logs/level']) !!}

    <h1>
        {!! $user->displayName !!}'s Level Logs
    </h1>

    {!! $logs->render() !!}
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
                    <div class="logs-table-cell">Old Level</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">New Level</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Log</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Date</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($logs as $log)
                <div class="logs-table-row">
                    @include('user._level_log_row', ['level' => $log, 'owner' => $user])
                </div>
            @endforeach
        </div>
    </div>
    {!! $logs->render() !!}
@endsection
