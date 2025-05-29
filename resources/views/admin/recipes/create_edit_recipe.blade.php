@extends('admin.layout')

@section('admin-title')
    Recipes
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Recipes' => 'admin/data/recipes', ($recipe->id ? 'Edit' : 'Create') . ' Recipe' => $recipe->id ? 'admin/data/recipes/edit/' . $recipe->id : 'admin/data/recipes/create']) !!}

    <h1>{{ $recipe->id ? 'Edit' : 'Create' }} Recipe
        @if ($recipe->id)
            <a href="#" class="btn btn-outline-danger float-right delete-recipe-button">Delete Recipe</a>
        @endif
    </h1>

    {!! Form::open(['url' => $recipe->id ? 'admin/data/recipes/edit/' . $recipe->id : 'admin/data/recipes/create', 'files' => true]) !!}

    <div class="row">
        <div class="col-md-4 form-group">
            {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
            <div>{!! Form::file('image') !!}</div>
            <div class="text-muted">Recommended size: 100px x 100px</div>
            @if ($recipe->has_image)
                <div class="form-check">
                    {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                    {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                </div>
            @endif
        </div>
        <div class="col-md-8 form-group">
            {!! Form::label('Name') !!}
            {!! Form::text('name', $recipe->name, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            {!! Form::checkbox('needs_unlocking', 1, $recipe->needs_unlocking, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Needs to be Unlocked', 'data-off' => 'Automatically Unlocked']) !!}
        </div>
        <div class="col-md-4 form-group">
            {!! Form::label('Time to Craft (Optional)') !!}
            <p>The amount of time (in minutes, e.g 1 hour -> 60) that a recipe will take to craft</p>
            {!! Form::number('time', $recipe->time, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-4 form-group">
            {{-- required slot --}}
            {!! Form::label('Required Slot (Optional)') !!}
            <p>The recipe slot that is required to craft this recipe. If left blank, no slot is required.</p>
            {!! Form::select('required_slot_id', $slots, $recipe->required_slot_id, ['class' => 'form-control', 'placeholder' => 'No Slot Required']) !!}
        </div>
    </div>
    <div class="alert alert-info">Recipes that have been unlocked (if set to require unlocking) cannot be crafted if the recipe is not ''open'' or is ''closed''</div>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('open_at', 'Open Time (Optional)') !!} {!! add_help('Recipes cannot be viewed or crafted until the starting time.') !!}
            {!! Form::text('open_at', $recipe->open_at, ['class' => 'form-control datepicker']) !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::label('close_at', 'Close Time (Optional)') !!} {!! add_help('Recipes cannot be viewed or crafted after the ending time.') !!}
            {!! Form::text('close_at', $recipe->close_at, ['class' => 'form-control datepicker']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $recipe->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $recipe->id ? $recipe->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the trait will not be visible in the trait list or available for selection in search and design updates. Permissioned staff will still be able to add them to characters, however.') !!}
    </div>

    <div class="row">
        <div class="col-md-6 col-12 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-header h3">Recipe Ingredients</div>
                <div class="card-body">
                    @include('widgets._recipe_ingredient_select', ['ingredients' => $recipe->ingredients])
                </div>
            </div>
        </div>
        <div class="col-md-6 col-12 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-header h3">Recipe Rewards</div>
                <div class="card-body">
                        @include('widgets._recipe_reward_select', ['rewards' => $recipe->rewards])
                </div>
            </div>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($recipe->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._recipe_ingredient_select_row', ['items' => $items, 'categories' => $categories, 'currencies' => $currencies])
    @include('widgets._recipe_reward_select_row', ['items' => $items, 'currencies' => $currencies, 'tables' => $tables, 'raffles' => $raffles])

    @if ($recipe->id)
        @include('widgets._add_limits', ['object' => $recipe])

        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._entry', [
                    'imageUrl' => $recipe->imageUrl,
                    'name' => $recipe->displayName,
                    'description' => $recipe->parsed_description,
                    'searchUrl' => $recipe->searchUrl,
                    'visible' => $recipe->is_visible,
                    'edit' => [
                        'title' => 'Recipe',
                        'object' => $recipe,
                    ],
                ])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    @include('js._recipe_reward_js')
    @include('js._recipe_ingredient_js')
    @include('widgets._datetimepicker_js')
    @include('js._tinymce_wysiwyg', ['tinymceSelector' => '.wysiwyg', 'tinymceHeight' => 300])
    <script>
        $(document).ready(function() {
            $('.delete-recipe-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/recipes/delete') }}/{{ $recipe->id }}", 'Delete Recipe');
            });
        });
    </script>
@endsection
