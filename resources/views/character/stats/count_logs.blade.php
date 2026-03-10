@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->slug }}'s Count Logs
@endsection

@section('profile-content')
    {!! breadcrumbs(['Characters' => 'characters', $character->slug => $character->url, 'Stat Information' => $character->url . '/stats', 'Stat Logs' => $character->url . '/stats/logs', 'Count Logs' => $character->url . '/stats/logs/count']) !!}

    <h1>
        {!! $character->displayName !!}'s Count Logs
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
                @include('character.stats._count_log_row', ['count' => $log, 'owner' => $character])
            @endforeach
        </tbody>
    </table>
    {!! $logs->render() !!}
@endsection
