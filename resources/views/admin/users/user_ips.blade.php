@extends('admin.layout')

@section('admin-title')
    User IP Index
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'User Index' => 'admin/users', 'User IP Index' => 'admin/users/ips']) !!}

    <h1>User IP Index</h1>

    <p>This page shows a collection of IPs and all users that share that IP.</p>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end align-items-start">
            <div class="form-group mr-sm-3 mb-3">
                {!! Form::text('ip', Request::get('ip'), ['class' => 'form-control', 'placeholder' => 'IP']) !!}
            </div>
            <div class="form-group mr-sm-3 mb-3">
                {!! Form::select('user_id', $users, Request::get('user_id'), ['class' => 'form-control selectize', 'placeholder' => 'Select User', 'style' => 'width: 250px']) !!}
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
                <div class="col-6 col-md-3">
                    <div class="logs-table-cell">IP</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="logs-table-cell">Last Used</div>
                </div>
                <div class="col-12 col-md">
                    <div class="logs-table-cell">Users</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($ips as $ip)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">
                                {!! $ip->isProxy ?? '' !!}
                                {!! $ip->isMobile ?? '' !!}
                                {!! $ip->isBanned ?? '' !!}
                                {{ $ip->ip }}
                            </div>
                            @if ($ip->is_mobile_network || $ip->is_user_banned)
                                <div>
                                    @if ($ip->is_mobile_network)
                                        <span class="badge badge-info">Mobile Network</span>
                                    @endif
                                    @if ($ip->is_user_banned)
                                        <span class="badge badge-danger">Banned</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">
                                {!! $ip->updated_at ? format_date($ip->updated_at) : 'Unknown' !!}
                            </div>
                        </div>
                        <div class="col-12 col-md">
                            <div class="logs-table-cell">
                                {!! $ip->usersString() ?? 'None' !!}
                            </div>
                        </div>
                        <div class="col-md-auto text-right py-1">
                            {!! Form::open(['url' => 'admin/users/ips/proxy', 'class' => 'd-inline']) !!}
                            {!! Form::hidden('ip', $ip->ip) !!}
                            {!! Form::hidden('value', $ip->is_known_proxy ? 0 : 1) !!}
                            {!! Form::submit('Proxy?', ['class' => 'btn btn-sm btn-outline-warning']) !!}
                            {!! Form::close() !!}
                            {!! Form::open(['url' => 'admin/users/ips/mobile', 'class' => 'd-inline ml-1']) !!}
                            {!! Form::hidden('ip', $ip->ip) !!}
                            {!! Form::hidden('value', $ip->is_mobile_network ? 0 : 1) !!}
                            {!! Form::submit('Mobile?', ['class' => 'btn btn-sm btn-outline-info']) !!}
                            {!! Form::close() !!}
                            @if ($ip->is_user_banned)
                                {!! Form::open(['url' => 'admin/users/ips/clear-ban', 'class' => 'd-inline ml-1']) !!}
                                {!! Form::hidden('ip', $ip->ip) !!}
                                {!! Form::submit('Clear IP Ban', ['class' => 'btn btn-sm btn-outline-danger']) !!}
                                {!! Form::close() !!}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {!! $ips->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $ips->count() }} IPs found.</div>
@endsection
@section('scripts')
    <script>
        $('.selectize').selectize();
    </script>
@endsection
