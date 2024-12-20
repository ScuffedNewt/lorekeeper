@php
    $wheel = $daily->wheel;    
@endphp

<h3>Images</h3>
<p> The images for the wheel! Keep in mind that if you use an image as the wheel, the segments must align with how a non-image wheel would look like, or your reward distribution will be off.</p>
<div class="form-group">
    @if ($wheel?->wheel_extension)
        <a href="{{ $wheel->wheelUrl }}"><img src="{{ $wheel->wheelUrl }}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
    @endif
    {!! Form::label('Wheel Image (Optional)') !!}
    <div class="custom-file">
        {!! Form::label('data[wheel_image]', 'Choose file...', ['class' => 'custom-file-label']) !!}
        {!! Form::file('data[wheel_image]', ['class' => 'custom-file-input']) !!}
    </div>
    <div class="text-muted">
        Recommended size: The size of your chosen Wheel. Make sure that the segments align correctly.
    </div>
    @if (isset($wheel?->wheel_extension))
        <div class="form-check">
            {!! Form::checkbox('data[remove_wheel]', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Wheel As-Is', 'data-on' => 'Remove Wheel Image']) !!}
        </div>
    @endif
</div>
<div class="form-group">
    @if ($wheel?->stopper_extension)
        <a href="{{ $wheel->stopperUrl }}"><img src="{{ $wheel->stopperUrl }}" class="w-100 float-left mr-3" style="max-height:125px;max-width:125px;"></a>
    @endif
    {!! Form::label('Stopper Image (Optional)') !!}
    <div class="custom-file">
        {!! Form::label('data[stopper_image]', 'Choose file...', ['class' => 'custom-file-label']) !!}
        {!! Form::file('data[stopper_image]', ['class' => 'custom-file-input']) !!}
    </div>
    <div class="text-muted">Recommended size: 50 x 50px.</div>
    @if (isset($wheel?->stopper_extension))
        <div class="form-check">
            {!! Form::checkbox('data[remove_stopper]', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Stopper As-Is', 'data-on' => 'Remove Stopper Image']) !!}
        </div>
    @endif
</div>
<div class="form-group">
    @if ($wheel?->background_extension)
        <a href="{{ $wheel->backgroundUrl }}"><img src="{{ $wheel->backgroundUrl }}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
    @endif
    {!! Form::label('Background Image (Optional)') !!} {!! add_help('This image is used as a wheel background and will take the place of the daily image.') !!}
    <div class="custom-file">
        {!! Form::label('data[background_image]', 'Choose file...', ['class' => 'custom-file-label']) !!}
        {!! Form::file('data[background_image]', ['class' => 'custom-file-input']) !!}
    </div>
    <div class="text-muted">Recommended size: Any, just play around until it looks good!</div>
    @if (isset($wheel?->background_extension))
        <div class="form-check">
            {!! Form::checkbox('data[remove_background]', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Background As-Is', 'data-on' => 'Remove Background Image']) !!}
        </div>
    @endif
</div>

<hr>
<h3>Wheel Style </h3>
<div class="row p-3">
    <div class="form-group col-lg col-6">
        {!! Form::number('data[size]', $wheel->size ?? 400, ['class' => 'form-control']) !!}
        {!! Form::label('data[size]', 'Size') !!} {!! add_help('The pixel size of the wheel.') !!}
    </div>
    <div class="form-group col-lg col-6">
        {!! Form::select('data[alignment]', ['center' => 'Center', 'left' => 'Left', 'right' => 'Right'], $wheel->alignment ?? 'center', ['class' => 'form-control']) !!}
        {!! Form::label('data[alignment]', 'Alignment') !!} {!! add_help('Whether the wheel should load on the left, right or center.') !!}
    </div>
    <div class="form-group col-lg col-6">
        {!! Form::number('data[segment_number]', $wheel->segment_number ?? 1, ['class' => 'form-control']) !!}
        {!! Form::label('data[segment_number]', 'Segment Number') !!} {!! add_help('How many segments does the wheel have?') !!}
    </div>
    <div class="form-group col-lg col-6">
        {!! Form::select('data[text_orientation]', ['curved' => 'Curved', 'vertical' => 'Vertical'], $wheel->text_orientation ?? 'curved', ['class' => 'form-control']) !!}
        {!! Form::label('data[text_orientation]', 'Text Orientation') !!} {!! add_help('How text on the wheel should be displayed.') !!}
    </div>
    <div class="form-group col-lg col-6">
        {!! Form::number('data[text_fontsize]', $wheel->text_fontsize ?? 24, ['class' => 'form-control']) !!}
        {!! Form::label('data[text_fontsize]', 'Text Font Size') !!} {!! add_help('Font size of the text on the wheel.') !!}
    </div>
</div>

@include('dailies._segment_style', ['segments' => $wheel->segmentStyles, 'totalSegments' => $wheel->segment_number])
