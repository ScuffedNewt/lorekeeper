@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s Stat Logs
@endsection

@section('profile-content')
    {!! breadcrumbs([
        $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
        $character->fullName => $character->url,
        'Stat Information' => $character->url . '/stats',
        'Stat Logs' => $character->url . '/stats/logs',
    ]) !!}

    <h1>
        {!! $character->displayName !!}'s Stat Logs
    </h1>

    <h3>Latest EXP Activity</h3>
    <table class="table table-sm">
        <thead>
            <th>Sender</th>
            <th>Quantity</th>
            <th>Log</th>
            <th>Date</th>
        </thead>
        <tbody>
            @foreach ($exps as $exp)
                @include('character.stats._exp_log_row', ['exp' => $exp, 'owner' => $character])
            @endforeach
        </tbody>
    </table>
    <div class="text-right">
        <a href="{{ url($character->url . '/stats/logs/experience') }}">View all...</a>
    </div>

    <h3>Latest Stat Activity</h3>
    <h5>Transfers</h5>
    <table class="table table-sm">
        <thead>
            <th>Sender</th>
            <th>Recipient</th>
            <th>Quantity</th>
            <th>Log</th>
            <th>Date</th>
        </thead>
        <tbody>
            @foreach ($stat_transfers as $stat)
                @include('character.stats._stat_transfer_log_row', ['stat' => $stat, 'owner' => $character])
            @endforeach
        </tbody>
    </table>
    <h5>Level Ups</h5>
    <table class="table table-sm">
        <thead>
            <th></th>
            <th>Stat</th>
            <th>Level</th>
            <th>Date</th>
        </thead>
        <tbody>
            @foreach ($stat_levels as $level)
                @include('character.stats._stat_level_log_row', ['stat' => $level, 'owner' => $character])
            @endforeach
        </tbody>
    </table>
    <div class="text-right">
        <a href="{{ url($character->url . '/stats/logs/stat-points') }}">View all...</a>
    </div>

    <h3>Latest Level-Up Activity</h3>
    <table class="table table-sm">
        <thead>
            <th></th>
            <th>Old Level</th>
            <th>New Level</th>
            <th>Date</th>
        </thead>
        <tbody>
            @foreach ($levels as $level)
                @include('character.stats._level_log_row', ['exp' => $level, 'owner' => $character])
            @endforeach
        </tbody>
    </table>
    <div class="text-right">
        <a href="{{ url($character->url . '/stats/logs/level') }}">View all...</a>
    </div>

    <h3>Latest Stat Value Adjustments</h3>
    <table class="table table-sm">
        <thead>
            <th>Sender</th>
            <th>Stat</th>
            <th>Quantity / New Count</th>
            <th>Log</th>
            <th>Date</th>
        </thead>
        <tbody>
            @foreach ($counts as $count)
                @include('character.stats._count_log_row', ['count' => $count, 'owner' => $character])
            @endforeach
        </tbody>
    </table>
    <div class="text-right">
        <a href="{{ url($character->url . '/stats/logs/count') }}">View all...</a>
    </div>
@endsection
