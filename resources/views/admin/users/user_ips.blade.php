@extends('admin.layout')

@section('admin-title')
    User IP Index
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'User Index' => 'admin/users', 'User IP Index' => 'admin/users/ips']) !!}

    <h1>User IP Index</h1>

    <p>This page shows a collection of IPs and all users that share that IP.</p>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-sm-3 mb-3">
            {!! Form::text('ip', Request::get('ip'), ['class' => 'form-control', 'placeholder' => 'IP']) !!}
        </div>
        <div class="form-group mr-sm-3 mb-3">
            {!! Form::select('user_id', $users, Request::get('user_id'), ['class' => 'form-control selectize', 'placeholder' => 'User']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select(
                'sort',
                [
                    'newest' => 'Newest',
                    'oldest' => 'Oldest',
                    'most_users' => 'Most Shared Users',
                    'closely_updated' => 'Closely Updated',
                ],
                Request::get('sort') ?: 'category',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    {!! $ips->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="logs-table-cell">IP</div>
                </div>
                <div class="col-4 col-md-4">
                    <div class="logs-table-cell">Last Used</div>
                </div>
                <div class="col-4 col-md-4">
                    <div class="logs-table-cell">Users</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($ips as $ip)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-12 col-md-4">
                            <div class="logs-table-cell">
                                {{ $ip->ip }}
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="logs-table-cell">
                                {!! $ip->updated_at ? format_date($ip->updated_at) : 'Unknown' !!}
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="logs-table-cell">
                                {!! $ip->usersString ?? 'None' !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {!! $ips->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $ips->count() }} IPs found.</div>
@endsection
