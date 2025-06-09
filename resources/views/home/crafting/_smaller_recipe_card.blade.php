<div class="col-md-4 mb-2">
    <div class="card h-100 alert-secondary py-0" data-id="{{ $recipe->id }}" data-name="{{ $recipe->name }}">
        <div class="d-flex justify-content-between align-items-center p-1">
            @if ($recipe->has_image)
                <div class="col-md-6">
                    <img src="{{ $recipe->imageUrl }}" class="img-fluid">
                </div>
            @endif
            <div class="col-md-{{ $recipe->has_image ? '6' : '12' }}">
                <h4 class="mb-0 mt-0 d-inline col-form-label">
                    {!! $recipe->displayName !!}
                </h4>
                <a class="btn btn-secondary btn-sm btn-craft" style="line-height:1;" href="">
                    Craft
                </a>
            </div>
        </div>
    </div>
</div>
