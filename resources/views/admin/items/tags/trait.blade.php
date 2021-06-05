<div class="text-right">
    <a href="#" class="btn btn-primary mb-3" id="add-feature">Add Trait</a>
</div>

<div id="featureList" class="form-group">
    @foreach($tag->getData()['features'] as $feature)
    @endforeach
</div>

<div class="feature-row hide mb-2">
{!! Form::select('feature[]',  $tag->getEditData()['features'], null, ['class' => 'form-control mr-2 feature-select selectsize']) !!}
{!! Form::number('quantity[]', 1, ['class' => 'form-control mr-2', 'min' => 1]) !!}
<a href="#" class="remove-feature btn btn-danger">Remove</a>
</div>

<div class="text-right">
    <a href="#" class="btn btn-primary mb-3" id="add-category">Add Trait Category</a>
</div>

<div id="categoryList" class="form-group">
</div>

<div class="category-row hide mb-2">
{!! Form::select('feature_type[]',  $tag->getEditData()['categories'], null, ['class' => 'form-control mr-2 category-select selectsize']) !!}
{!! Form::number('type_quantity[]', 1, ['class' => 'form-control mr-2', 'min' => 1]) !!}
<a href="#" class="remove-category btn btn-danger">Remove</a>
</div>

@section('scripts')
<script>
    $( document ).ready(function() {  
        $('#add-category').on('click', function(e) {
            e.preventDefault();
            addCategoryRow();
        });
        $('.remove-category').on('click', function(e) {
            e.preventDefault();
            removeCategoryRow($(this));
        });
        function addCategoryRow() {
            var $clone = $('.category-row').clone();
            $('#categoryList').append($clone);
            $clone.removeClass('hide category-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-category').on('click', function(e) {
                e.preventDefault();
                removeCategoryRow($(this));
            })
            $clone.find('.category-select').selectize();
        }
        function removeCategoryRow($trigger) {
            $trigger.parent().remove();
        }   

        $('#add-feature').on('click', function(e) {
            e.preventDefault();
            addFeatureRow();
        });
        $('.remove-feature').on('click', function(e) {
            e.preventDefault();
            removeFeatureRow($(this));
        });
        function addFeatureRow() {
            var $clone = $('.feature-row').clone();
            $('#featureList').append($clone);
            $clone.removeClass('hide feature-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-feature').on('click', function(e) {
                e.preventDefault();
                removeFeatureRow($(this));
            })
            $clone.find('.feature-select').selectize();
        }
        function removeFeatureRow($trigger) {
            $trigger.parent().remove();
        }   
          
    });
    
    $('.selectize').selectize();
    
    </script>
@endsection
