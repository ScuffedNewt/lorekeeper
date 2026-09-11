@php
    $labels = [];
    foreach (config('lorekeeper.character_labels') as $key => $value) {
        $labels[$key] = $key . ': ' . $value;
    }

    $image = isset($image) && $image?->label ? $image : null;
@endphp

<hr />

<h3>Label</h3>

@if (!$isStaff)
    <p>Please verify if this character is inspired by existing source material. If this character is inspired by existing source material, please provide the name of the media or character.</p>
    <p>Failure to identify this information may result in the character being removed from the site without prior notice.</p>
    <div class="form-check mb-2">
        {!! Form::checkbox('is_inspired', 1, $image ? 1 : 0, ['class' => 'form-check-input inspired-toggle', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_inspired', 'Is This Character Inspired By Any Existing Source Material?', ['class' => 'form-check-label ml-3']) !!}
    </div>
@endif

<div class="{{ $isStaff || $image ? '' : 'hide' }}" id="inspired-info">
    <p>The labels section are used to identify anything of note about a character's design. It is saved per-image, and is preserved until manually changed.</p>

    <div class="form-group">
        {!! Form::label('Label') !!}
        {!! Form::select('label', $labels, $image ? $image->label['label'] : null, ['class' => 'form-control label-select', 'placeholder' => 'Select an Applicable Label']) !!}
    </div>

    <div class="form-group {{ $image && $image->label['label_information'] ? '' : 'hide' }}" id="label-info">
        <p>
            For IP inspired, describe the media, ex. "Shinji Ikari from Neon Genesis Evangelion".
            <br />For other labels, describe the character or link to the character's page.
        </p>
        {!! Form::label('Label Information (Optional)') !!}
        {!! Form::text('label_information', $image ? $image->label['label_information'] : null, ['class' => 'form-control', 'placeholder' => 'Enter Label Information (ex. link to character, or describe media inspiration)']) !!}
    </div>
</div>

<script>
    $('.inspired-toggle').change(function() {
        if ($(this).is(':checked')) {
            $('#inspired-info').removeClass('hide');
            $('.label-select').prop('required', true);
        } else {
            $('#inspired-info').addClass('hide');
            $('#inspired-info input').prop('required', false);
        }
    });

    $(".label-select").change(function() {
        var label = $('.label-select').val();
        if (label) {
            $('#label-info').removeClass('hide');
        } else {
            $('#label-info').addClass('hide');
        }
    });
</script>
