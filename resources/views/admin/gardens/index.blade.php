@extends('admin.layout')

@section('admin-title')
    Gardens
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Gardens' => 'admin/gardens']) !!}

    <h1>Gardens</h1>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/gardens/create') }}">
            <i class="fas fa-plus"></i> Create New Garden
        </a>
    </div>

    @if (!count($gardens))
        <p>No gardens found.</p>
    @else
        <div class="row ml-md-2 mb-4">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                <div class="col-4 col-md-4 font-weight-bold">Name</div>
                <div class="col-4 col-md-4 font-weight-bold">No. Plots</div>
            </div>
            @foreach ($gardens as $garden)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-4 col-md-4"> {!! $garden->displayName !!}</div>
                    <div class="col-4 col-md-4">{{ $garden->plots->count() }}</div>
                    <div class="col-4 col-md-4 text-right">
                        <a href="{{ url('admin/gardens/edit/' . $garden->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
