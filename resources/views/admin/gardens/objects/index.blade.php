@extends('admin.layout')

@section('admin-title')
    Garden Objects
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Gardens' => 'admin/gardens', 'Garden Objects' => 'admin/gardens/objects']) !!}

    <h1>Garden Objects</h1>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/gardens/objects/create') }}"><i class="fas fa-plus"></i> Create New Object</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('allowed_object_types', getGardenObjectTypes(), Request::get('allowed_object_types'), ['class' => 'form-control', 'placeholder' => 'Select Allowed Object Type']) !!}
            </div>
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($objects))
        <p>No objects found.</p>
    @else
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-4 col-md-4">
                        <div class="logs-table-cell">Type</div>
                    </div>
                    <div class="col-5 col-md-6">
                        <div class="logs-table-cell">Object</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($objects as $object)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-4 col-md-4">
                                <div class="logs-table-cell">
                                    {{ ucwords($object->type) }}
                                </div>
                            </div>
                            <div class="col-5 col-md-6">
                                <div class="logs-table-cell">
                                    {!! $object->object->displayName ?? $objet->object->name !!}
                                </div>
                            </div>
                            <div class="col-3 col-md-2 text-right">
                                <div class="logs-table-cell">
                                    <a href="{{ url('admin/gardens/objects/edit/' . $object->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
