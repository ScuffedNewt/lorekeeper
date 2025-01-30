@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Level
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Stats' => $user->url . '/stats']) !!}

    <h1>
        {!! $user->displayName !!}'s Stat Information
    </h1>

    @include('widgets._level_info', ['level' => $user->level])

    <div class="container mb-3 text-right">
        @if (Auth::check() && Auth::user()->id == $user->id)
            <a href="{{ url('user-stats') }}">
                <div class="btn btn-primary mr-0">
                    Go to Personal Stat Page
                </div>
            </a>
        @endif
    </div>

    <h3>Latest EXP Activity</h3>
    <table class="table table-sm">
        <thead>
            <th>Sender</th>
            <th>Recipient</th>
            <th>Quantity</th>
            <th>Log</th>
            <th>Date</th>
        </thead>
        <tbody>
            @foreach ($exps as $exp)
                @include('user._exp_log_row', ['exp' => $exp, 'owner' => $user])
            @endforeach
        </tbody>
    </table>
    <div class="text-right">
        <a href="{{ url($user->url . '/stats/logs/exp') }}">View all...</a>
    </div>

    <h3>Latest Stat Activity</h3>
    <table class="table table-sm">
        <thead>
            <th>Sender</th>
            <th>Recipient</th>
            <th>Quantity</th>
            <th>Log</th>
            <th>Date</th>
        </thead>
        <tbody>
            @foreach ($stats as $stat)
                @include('user._stat_log_row', ['exp' => $stat, 'owner' => $user])
            @endforeach
        </tbody>
    </table>
    <div class="text-right">
        <a href="{{ url($user->url . '/stats/logs/points') }}">View all...</a>
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
                @include('user._level_log_row', ['exp' => $level, 'owner' => $user])
            @endforeach
        </tbody>
    </table>
    <div class="text-right">
        <a href="{{ url($user->url . '/stats/logs/level') }}">View all...</a>
    </div>
@endsection
