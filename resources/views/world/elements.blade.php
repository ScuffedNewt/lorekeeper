@extends('world.layout')

@section('title')
    Elements
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Elements' => 'world/elements']) !!}
    <h1>Elements</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'alpha' => 'Sort Alphabetically (A-Z)',
                        'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                    ],
                    Request::get('sort') ?: 'alpha',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $elements->render() !!}
    @foreach ($elements as $element)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._element_entry', [
                    'imageUrl' => $element->imageUrl,
                    'name' => $element->displayName,
                    'description' => $element->parsed_description,
                    'idUrl' => $element->idUrl,
                    'searchUrl' => $element->searchUrl,
                ])
            </div>
        </div>
    @endforeach
    {!! $elements->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $elements->total() }} result{{ $elements->total() == 1 ? '' : 's' }} found.</div>
@endsection
