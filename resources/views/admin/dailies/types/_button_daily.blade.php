<h3>Options</h3>

<div class="row">
    <div class="form-group col">
        {!! Form::checkbox('data[is_loop]', 1, $daily->data['is_loop'] ?? 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('data[is_loop]', 'Set Loop', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
            'If turned off, each of
                        the ' .
                __('dailies.daily') .
                ' rewards will only be able to be claimed once.',
        ) !!}
    </div>
    <div class="form-group col">
        {!! Form::checkbox('data[is_streak]', 1, $daily->data['is_streak'] ?? 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('data[is_streak]', 'Is Streak', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, should the user miss a day of claiming, the rewards start over from day 1.') !!}
    </div>
</div>

<hr />

<h3>Images</h3>
<div class="form-group">
    {!! Form::label('Button Image (Optional)') !!} {!! add_help('This image is used for the button instead of the generic Collect Reward button.') !!}
    <div class="custom-file">
        {!! Form::label('data[button_image]', 'Choose file...', ['class' => 'custom-file-label']) !!}
        {!! Form::file('data[button_image]', ['class' => 'custom-file-input']) !!}
    </div>
    <div class="text-muted">Recommended size: 200 x 200px. File type: png.</div>
    @if ($daily->data['has_button_image'] ?? false)
        <div class="form-check">
            {!! Form::checkbox('remove_button_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_button_image', 'Remove current button image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>
