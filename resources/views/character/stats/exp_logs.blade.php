@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->slug }}'s EXP Logs
@endsection

@section('profile-content')
    {!! breadcrumbs(['Characters' => 'characters', $character->slug => $character->url, 'Stat Information' => $character->url . '/stats',
    'Experience Logs' => $character->url . '/stats/logs/experience']) !!}

    <h1>
        {!! $character->displayName !!}'s Experience Logs
    </h1>

    {!! $logs->render() !!}
    <table class="table table-sm">
        <thead>
            <th>Sender</th>
            <th>Recipient</th>
            <th>Quantity</th>
            <th>Log</th>
            <th>Date</th>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                @include('character.stats._exp_log_row', ['exp' => $log, 'owner' => $character])
            @endforeach
        </tbody>
    </table>
    {!! $logs->render() !!}
@endsection
