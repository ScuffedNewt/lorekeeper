<div class="addon-row hide mb-2">
    {!! Form::select('user_item_id[]', $addons, null, ['class' => 'form-control mr-2 addon-select', 'placeholder' => 'Select Item']) !!}
    {!! Form::text('user_item_data[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
    <a href="#" class="remove-addon btn btn-danger mb-2">×</a>
</div>