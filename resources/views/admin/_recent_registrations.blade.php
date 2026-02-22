<div class="card p-3">
    <div class="row no-gutters justify-content-between">
        <div class="col-auto p-1">
            <h4 class="mb-1">
                10 Recent Registrations
            </h4>
        </div>
        <div class="col p-1 text-right">
            <a href="{{ url('admin/users/ips') }}" class="btn btn-primary btn-sm">
                User IP Index
            </a>
        </div>
    </div>

    <div class="logs-table">
        <div class="logs-table-header">
            <div class="row no-gutters">
                <div class="col-6 col-md-3">
                    <div class="logs-table-cell text-truncate">
                        Username
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">
                        Registered
                    </div>
                </div>
                <div class="col col-md-3">
                    <div class="logs-table-cell">
                        IP Address{!! add_help('This is the first recorded IP address for this user.') !!}
                    </div>
                </div>
                <div class="col col-md">
                    <div class="logs-table-cell">
                        Shared With
                    </div>
                </div>
            </div>
        </div>
        <div class="logs-table-body" style="max-height: 200px; overflow: auto;">
            @foreach ($recentUsers as $user)
                <div class="logs-table-row">
                    <div class="row no-gutters flex-wrap">
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell text-truncate">
                                {!! $user->displayName !!}
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="logs-table-cell">
                                {!! pretty_date($user->created_at) !!}
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="logs-table-cell">
                                {!! $user->ips()->first()->ip ?? '' !!}
                            </div>
                        </div>
                        <div class="col-12 col-md">
                            <div class="logs-table-cell">
                                {!! $user->ips()->first() &&
                                count(
                                    $user->ips()->first()->users->where('id', '!=', $user->id)->pluck('displayName')->toArray(),
                                )
                                    ? implode(
                                        ', ',
                                        $user->ips()->first()->users->where('id', '!=', $user->id)->pluck('displayName')->toArray(),
                                    )
                                    : '<span class="text-muted">---</span>' !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
