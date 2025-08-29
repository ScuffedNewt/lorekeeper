@extends('admin.layout')

@section('admin-title')
    Runbooks
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Runbooks' => 'admin/runbooks']) !!}

    <h1>Runbooks</h1>

    <p>

    </p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/runbooks/create') }}"><i class="fas fa-plus"></i> Create New Runbook</a>
    </div>

    @if (!count($runbooks))
        <p>No runbooks found.</p>
    @else
        {!! $runbooks->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="logs-table-cell">Title</div>
                    </div>
                    <div class="col-3 col-md-4">
                        <div class="logs-table-cell">Type</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Last Edited</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($runbooks as $runbook)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-12 col-md-4">
                                <div class="logs-table-cell"><a href="{{ $runbook->url }}">{{ $runbook->title }}</a></div>
                            </div>
                            <div class="col-3 col-md-4">
                                <div class="logs-table-cell">{{ $runbook->type }}</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="logs-table-cell">{!! pretty_date($runbook->updated_at) !!}</div>
                            </div>
                            <div class="col-3 col-md-1 text-right">
                                <div class="logs-table-cell"><a href="{{ url('admin/runbooks/edit/' . $runbook->id) }}" class="btn btn-primary py-0 px-2">Edit</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $runbooks->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $runbooks->total() }} result{{ $runbooks->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection
