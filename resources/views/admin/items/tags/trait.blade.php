<div class="text-right">
    <a href="#" class="btn btn-primary mb-3" id="add-feature">Add Trait</a>
</div>

<p>Any traits added will be available to the user to select.</p>
<div id="featureList" class="form-group">
    @foreach($tag->getData()['feature'] as $feature)
        {!! Form::select('feature[]', $tag->getEditData()['features'], $feature, ['class' => 'form-control mr-2 selectize']) !!}
        <a href="#" class="remove-feature btn btn-danger mb-2">Remove</a>
    @endforeach
    <hr>
</div>

<div class="feature-row hide mb-2">
    {!! Form::select('feature[]', $tag->getEditData()['features'], null, ['class' => 'form-control mr-2 feature-select', 'placeholder' => 'Select a Trait']) !!}
    <a href="#" class="remove-feature btn btn-danger">Remove</a>
</div>

<div class="text-right">
    <a href="#" class="btn btn-primary mb-3" id="add-category">Add Trait Category</a>
</div>

<p>Any traits within the selected categories added will be available to the user to select.</p>
<div id="categoryList" class="form-group">
</div>

<div class="category-row hide mb-2">
    {!! Form::select('feature_type[]',  $tag->getEditData()['categories'], null, ['class' => 'form-control mr-2 category-select selectize', 'placeholder' => 'Select a Category']) !!}
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
