@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s EXP Logs
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Stat Information' => $user->url . '/stats', 'Experience Logs' => $user->url . '/stats/logs/experience']) !!}

    <h1>
        {!! $user->displayName !!}'s Experience Logs
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
                @include('user._exp_log_row', ['exp' => $log, 'owner' => $user])
            @endforeach
        </tbody>
    </table>
    {!! $logs->render() !!}
@endsection
