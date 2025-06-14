<div class="col-md-4 p-1 mb-2">
    <div class="card alert-secondary rounded-0 p-2 col-form-label h-100" data-id="{{ $recipe->id }}" data-name="{{ $recipe->name }}">
        <div class="row no-gutters align-items-center">
            @if (isset($recipe->image_url))
                <div class="col-auto mb-1 p-1">
                    <div class="recipe-icon-container">
                        <img src="{{ $recipe->imageUrl }}" class="recipe-image img-fluid mh-100" alt="{{ $recipe->name }}">
                    </div>
                </div>
            @endif
            <div class="col {{ isset($recipe->image_url) ? 'pl-2' : '' }} mb-1">
                <h4 class="mb-0 mt-0 d-inline col-form-label">
                    {!! $recipe->displayName !!}
                </h4>
            </div>
        </div>
        <div class="row no-gutters align-items-center mt-auto">
            <div class="col text-center">
                <a class="btn btn-secondary btn-sm btn-craft w-100" style="line-height: 1;" href="#">
                    Craft
                </a>
            </div>
            <div class="col-3 pl-1 d-flex flex-column h-100">
                @if ($recipe->checkRecipe(Auth::user()))
                    <span class="badge btn-sm btn-success d-flex align-items-center justify-content-center h-100 w-100" data-toggle="tooltip" title="You have the ingredients to craft this recipe!" style="font-size: 90%;">
                        <i class="fas fa-check"></i>
                    </span>
                @else
                    <span class="badge btn-sm btn-danger d-flex align-items-center justify-content-center h-100 w-100" data-toggle="tooltip" title="You don't have all the ingredients for this recipe yet..." style="font-size: 90%;">
                        <i class="fas fa-times"></i>
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
