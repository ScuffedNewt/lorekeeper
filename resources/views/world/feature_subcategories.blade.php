@extends('world.layout')

@section('title')
    Trait Subcategories
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Trait Subcategories' => 'world/trait-subcategories']) !!}
    <h1>Trait Subcategories</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    {!! $subcategories->render() !!}
    @foreach ($subcategories as $subcategory)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._entry', ['imageUrl' => $subcategory->subcategoryImageUrl, 'name' => $subcategory->displayName, 'description' => $subcategory->parsed_description, 'searchUrl' => $subcategory->searchUrl])
            </div>
        </div>
    @endforeach
    {!! $subcategories->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $subcategories->total() }} result{{ $subcategories->total() == 1 ? '' : 's' }} found.</div>
@endsection
