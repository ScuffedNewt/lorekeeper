@extends('admin.layout')

@section('admin-title')
    Recipes
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Recipes' => 'admin/data/recipes']) !!}

    <h1>Recipes</h1>

    <p>This is a list of recipes in the game that can be used to craft items.</p>

    <div class="text-right mb-3">
        <a class="btn btn-secondary" href="{{ url('admin/data/recipes/slots') }}"><i class="fas fa-cubes"></i> Crafting Recipe Slots</a>
        <a class="btn btn-secondary" href="{{ url('admin/data/recipe-categories') }}"><i class="fas fa-folder-open"></i> Recipe Categories</a>
        <a class="btn btn-primary" href="{{ url('admin/data/recipes/create') }}"><i class="fas fa-plus"></i> Create New Recipe</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
                {!! Form::select('recipe_category_id', $recipeCategories, Request::get('recipe_category_id'), ['class' => 'form-control', 'placeholder' => 'Any Category']) !!}
            </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($recipes))
        <p>No recipes found.</p>
    @else
        {!! $recipes->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-5 col-md-6">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-5 col-md-5">
                        <div class="logs-table-cell">Category</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($recipes as $recipe)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-5 col-md-6">
                                <div class="logs-table-cell">
                                    @if (!$recipe->is_visible)
                                        <i class="fas fa-eye-slash mr-1"></i>
                                    @endif
                                    {{ $recipe->name }}
                                </div>
                            </div>
                            <div class="col-4 col-md-5">
                                <div class="logs-table-cell">{{ $recipe->category ? $recipe->category->name : '' }}</div>
                            </div>
                            <div class="col-3 col-md-1 text-right">
                                <div class="logs-table-cell">
                                    <a href="{{ url('admin/data/recipes/edit/' . $recipe->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $recipes->render() !!}
    @endif

@endsection

@section('scripts')
    @parent
@endsection
