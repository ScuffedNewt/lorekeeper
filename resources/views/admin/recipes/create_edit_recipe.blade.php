@extends('admin.layout')

@section('admin-title') Recipes @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Recipes' => 'admin/data/recipes', ($recipe->id ? 'Edit' : 'Create').' Recipe' => $recipe->id ? 'admin/data/recipes/edit/'.$recipe->id : 'admin/data/recipes/create']) !!}

<h1>{{ $recipe->id ? 'Edit' : 'Create' }} Recipe
    @if($recipe->id)
        <a href="#" class="btn btn-outline-danger float-right delete-recipe-button">Delete Recipe</a>
    @endif
</h1>

{!! Form::open(['url' => $recipe->id ? 'admin/data/recipes/edit/'.$recipe->id : 'admin/data/recipes/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $recipe->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: 100px x 100px</div>
    @if($recipe->has_image)
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>

<div class="form-group mb-2">
{!! Form::checkbox('needs_unlocking', 1, $recipe->needs_unlocking, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Needs to be Unlocked', 'data-off' => 'Automatically Unlocked']) !!}
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('open_att', 'Open Time (Optional)') !!} {!! add_help('Recipes cannot be viewed or crafted until the starting time.') !!}
            {!! Form::text('open_at', $recipe->open_at, ['class' => 'form-control datepicker']) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('close_at', 'Close Time (Optional)') !!} {!! add_help('Recipes cannot be viewed or crafted after the ending time.') !!}
            {!! Form::text('close_at', $recipe->close_at, ['class' => 'form-control datepicker']) !!}
        </div>
    </div>
</div>
<div class="alert alert-info">Recipes that have been unlocked (if set to require unlocking) cannot be crafted if the recipe is not ''open'' or is ''closed''</div>

<div class="form-group">
    {!! Form::label('Time to Craft (Optional)') !!} <p>The amount of time (in minutes, e.g 1 hour -> 60) that a recipe will take to craft</p>
    {!! Form::number('time', $recipe->time, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    {!! Form::textarea('description', $recipe->description, ['class' => 'form-control wysiwyg']) !!}
</div>

<h3>Restrict Recipe</h3>
    <div class="form-group">
        {!! Form::checkbox('is_limited', 1, $recipe->is_limited, ['class' => 'is-limited-class form-check-label', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_limited', 'Should this recipe have a requirement?', ['class' => 'is-limited-label form-check-label ml-3']) !!} {!! add_help('If turned on, the recipe cannot be used/crafted unless the user currently owns all required items.') !!}
    </div>

    <div class="br-form-group mb-1" style="display: none">
        @include('widgets._recipe_limit_select', ['limits' => $recipe->limits, 'showRecipes' => true])
    </div>

<h3>Recipe Ingredients</h3>
@include('widgets._recipe_ingredient_select', ['ingredients' => $recipe->ingredients])

<hr>

<h3>Recipe Rewards</h3>
@include('widgets._recipe_reward_select', ['rewards' => $recipe->rewards])

<div class="text-right">
    {!! Form::submit($recipe->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@include('widgets._recipe_ingredient_select_row', ['items' => $items, 'categories' => $categories, 'currencies' => $currencies])
@include('widgets._recipe_reward_select_row', ['items' => $items, 'currencies' => $currencies, 'tables' => $tables, 'raffles' => $raffles])
@include('widgets._recipe_limit_row', ['items' => $items, 'currencies' => $currencies, 'recipes' => $recipes])

@if($recipe->id)
    <h3>Preview</h3>
    <div class="card mb-3">
        <div class="card-body">
            @include('world._entry', ['imageUrl' => $recipe->imageUrl, 'name' => $recipe->displayName, 'description' => $recipe->parsed_description, 'searchUrl' => $recipe->searchUrl])
        </div>
    </div>
@endif

@endsection

@section('scripts')
@parent
@include('js._recipe_limit_js')
@include('js._recipe_reward_js')
@include('js._recipe_ingredient_js')
<script>
$( document ).ready(function() {    
    $('.delete-recipe-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/recipes/delete') }}/{{ $recipe->id }}", 'Delete Recipe');
    });

    $('.is-limited-class').change(function(e){
        console.log(this.checked)
        $('.br-form-group').css('display',this.checked ? 'block' : 'none')
            })
        $('.br-form-group').css('display',$('.is-limited-class').prop('checked') ? 'block' : 'none')

        $( ".datepicker" ).datetimepicker({
        dateFormat: "yy-mm-dd",
        timeFormat: 'HH:mm:ss',
    });
});
    
</script>
@endsection