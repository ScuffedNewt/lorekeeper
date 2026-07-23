@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->slug }}'s Level Logs
@endsection

@section('profile-content')
    {!! breadcrumbs(['Characters' => 'characters', $character->slug => $character->url, 'Stat Information' => $character->url . '/stats', 'Stat Logs' => $character->url . '/stats/logs', 'Level Logs' => $character->url . '/stats/logs/level']) !!}

    <h1>
        {!! $character->displayName !!}'s Level Logs
    </h1>

    {!! $logs->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-6 col-md-2"><div class="logs-table-cell">Sender</div></div>
                <div class="col-6 col-md-2"><div class="logs-table-cell">Recipient</div></div>
                <div class="col-6 col-md-2"><div class="logs-table-cell">Old Level</div></div>
                <div class="col-6 col-md-2"><div class="logs-table-cell">New Level</div></div>
                <div class="col-6 col-md-2"><div class="logs-table-cell">Log</div></div>
                <div class="col-6 col-md-2"><div class="logs-table-cell">Date</div></div>
                </div>
            </div>
        <div class="logs-table-body">
            @foreach ($logs as $log)
                <div class="logs-table-row">
                    @include('character.stats._level_log_row', ['level' => $log, 'owner' => $character])
                </div>
            @endforeach
        </div>
    </div>
    {!! $logs->render() !!}
@endsection
