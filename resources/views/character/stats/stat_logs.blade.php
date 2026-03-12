@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->slug }}'s Stat Activity Logs
@endsection

@section('profile-content')
    {!! breadcrumbs([
        'Characters' => 'characters',
        $character->slug => $character->url,
        'Stat Information' => $character->url . '/stats',
        'Stat Logs' => $character->url . '/stats/logs',
        'Stat Activity Logs' => $character->url . '/stats/logs/stat-points',
    ]) !!}

    <h1>
        {!! $character->displayName !!}'s Stat Activity Logs
    </h1>

    <h3>Transfers</h3>
    {!! $transfers->render() !!}
    <table class="table table-sm">
        <thead>
            <th>Sender</th>
            <th>Recipient</th>
            <th>Quantity</th>
            <th>Log</th>
            <th>Date</th>
        </thead>
        <tbody>
            @foreach ($transfers as $log)
                @include('character.stats._stat_transfer_log_row', ['stat' => $log, 'owner' => $character])
            @endforeach
        </tbody>
    </table>
    {!! $transfers->render() !!}

    <h3>Level Ups</h3>
    {!! $levels->render() !!}
    <table class="table table-sm">
        <thead>
            <th>Sender</th>
            <th>Recipient</th>
            <th>Quantity</th>
            <th>Log</th>
            <th>Date</th>
        </thead>
        <tbody>
            @foreach ($levels as $log)
                @include('character.stats._stat_level_log_row', ['stat' => $log, 'owner' => $character])
            @endforeach
        </tbody>
    </table>
    {!! $levels->render() !!}
@endsection
