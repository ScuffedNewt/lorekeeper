<h3>Options</h3>

<div class="row p-4">
    <div class="form-group col">
        {!! Form::select('daily_timeframe', ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'], $daily ? $daily->daily_timeframe : 0, ['class' => 'form-control stock-field', 'data-name' => 'daily_timeframe']) !!}
        {!! Form::label('daily_timeframe', 'Daily Timeframe') !!} {!! add_help('This is the timeframe that the daily can
                be collected in. I.E. yearly will only allow one roll per year. Weekly allows one roll per week. Rollover will
                happen on UTC time.') !!}
    </div>
    <div class="form-group col">
        {!! Form::select('progress_display', ['none' => 'None', 'all' => 'All rewards shown'], $daily ? $daily->progress_display : 0, ['class' => 'form-control stock-field', 'data-name' => 'progress_display']) !!}
        {!! Form::label('progress_display', 'Prize Display') !!} {!! add_help('Decides what kind of information
                on the rewards for each segment should be shown on the daily page.') !!}
    </div>
    <div class="form-group col">
        {!! Form::checkbox('is_active', 1, $daily->id ? $daily->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
            'If turned
                off,
                the ' .
                __('dailies.daily') .
                ' will not be visible to regular users.',
        ) !!}
    </div>
</div>

<div class="pl-4">
    <div class="form-group">
        {!! Form::checkbox('is_timed_daily', 1, $daily->is_timed_daily ?? 0, [
            'class' => 'form-check-input daily-timed
                daily-toggle daily-field',
            'id' => 'is_timed_daily',
        ]) !!}
        {!! Form::label('is_timed_daily', 'Set Timed ' . __('dailies.daily'), ['class' => 'form-check-label ml-3']) !!}
        {!! add_help('Sets the ' . __('dailies.daily') . ' as timed between the chosen dates.') !!}
    </div>
    <div class="daily-timed-quantity {{ $daily->is_timed_daily ? '' : 'hide' }}">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('start_at', 'Start Time') !!} {!! add_help(
                        'The ' .
                            __('dailies.daily') .
                            ' will cycle
                                        in at this date.',
                    ) !!}
                    {!! Form::text('start_at', $daily->start_at, ['class' => 'form-control', 'id' => 'datepicker2']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('end_at', 'End Time') !!} {!! add_help(
                        'The ' .
                            __('dailies.daily') .
                            ' will cycle out
                                        at this date.',
                    ) !!}
                    {!! Form::text('end_at', $daily->end_at, ['class' => 'form-control', 'id' => 'datepicker3']) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<h3>Images</h3>
<p> The images for the wheel! Keep in mind that if you use an image as the wheel, the segments must align with how a non-image wheel would look like, or your reward distribution will be off.</p>
<div class="card-body row">
    <div class="form-group col-md-6">
        @if ($daily->has_image)
            <a href="{{ $daily->imageUrl }}"><img src="{{ $daily->dailyImageUrl }}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
        @endif
        {!! Form::label(__('dailies.daily') . ' Image (Optional)') !!} {!! add_help('This image is used on the ' . __('dailies.daily') . ' index.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: None (Choose a standard size for all {{ __('dailies.daily') }} images). File type: png.</div>
        @if ($daily->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Daily Image As-Is', 'data-on' => 'Remove Daily Image']) !!}
            </div>
        @endif
    </div>
    <div class="form-group col-md-6">
        @if ($wheel?->wheel_extension)
            <a href="{{ $wheel->wheelUrl }}"><img src="{{ $wheel->wheelUrl }}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
        @endif
        {!! Form::label('Wheel Image (Optional)') !!}
        <div>{!! Form::file('wheel_image') !!}</div>
        <div class="text-muted">Recommended size: The size of your chosen Wheel. Make sure that the segments align
            correctly.</div>
        @if (isset($wheel?->wheel_extension))
            <div class="form-check">
                {!! Form::checkbox('remove_wheel', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Wheel As-Is', 'data-on' => 'Remove Wheel Image']) !!}
            </div>
        @endif
    </div>

    <div class="form-group col-md-6">
        @if ($wheel?->stopper_extension)
            <a href="{{ $wheel->stopperUrl }}"><img src="{{ $wheel->stopperUrl }}" class="w-100 float-left mr-3" style="max-height:125px;max-width:125px;"></a>
        @endif
        {!! Form::label('Stopper Image (Optional)') !!}
        <div>{!! Form::file('stopper_image') !!}</div>
        <div class="text-muted">Recommended size: 50 x 50px.</div>
        @if (isset($wheel?->stopper_extension))
            <div class="form-check">
                {!! Form::checkbox('remove_stopper', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Stopper As-Is', 'data-on' => 'Remove Stopper Image']) !!}
            </div>
        @endif
    </div>

    <div class="form-group col-md-6">
        @if ($wheel?->background_extension)
            <a href="{{ $wheel->backgroundUrl }}"><img src="{{ $wheel->backgroundUrl }}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
        @endif
        {!! Form::label('Background Image (Optional)') !!} {!! add_help('This image is used as a wheel background and will take the place of the daily image.') !!}
        <div>{!! Form::file('background_image') !!}</div>
        <div class="text-muted">Recommended size: Any, just play around until it looks good!</div>
        @if (isset($wheel?->background_extension))
            <div class="form-check">
                {!! Form::checkbox('remove_background', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Background As-Is', 'data-on' => 'Remove Background Image']) !!}
            </div>
        @endif
    </div>
</div>

<hr>
<h3>Wheel Style </h3>
<div class="row p-3">
    <div class="form-group col-lg col-6">
        {!! Form::number('size', $wheel->size ?? 400, ['class' => 'form-control']) !!}
        {!! Form::label('size', 'Size') !!} {!! add_help('The pixel size of the wheel.') !!}
    </div>
    <div class="form-group col-lg col-6">
        {!! Form::select('alignment', ['center' => 'Center', 'left' => 'Left', 'right' => 'Right'], $wheel->alignment ?? 'center', ['class' => 'form-control']) !!}
        {!! Form::label('alignment', 'Alignment') !!} {!! add_help('Whether the wheel should load on the left, right or center.') !!}
    </div>
    <div class="form-group col-lg col-6">
        {!! Form::number('segment_number', $wheel->segment_number ?? 1, ['class' => 'form-control']) !!}
        {!! Form::label('segment_number', 'Segment Number') !!} {!! add_help('How many segments does the wheel have?') !!}
    </div>
    <div class="form-group col-lg col-6">
        {!! Form::select('text_orientation', ['curved' => 'Curved', 'vertical' => 'Vertical'], $wheel->text_orientation ?? 'curved', ['class' => 'form-control']) !!}
        {!! Form::label('text_orientation', 'Text Orientation') !!} {!! add_help('How text on the wheel should be displayed.') !!}
    </div>
    <div class="form-group col-lg col-6">
        {!! Form::number('text_fontsize', $wheel->text_fontsize ?? 24, ['class' => 'form-control']) !!}
        {!! Form::label('text_fontsize', 'Text Font Size') !!} {!! add_help('Font size of the text on the wheel.') !!}
    </div>
</div>


@include('dailies._segment_style', ['segments' => $wheel->segmentStyles, 'totalSegments' => $wheel->segment_number])

<hr>

<h3>Rewards</h3>
<p>Please add what reward the {{ __('dailies.daily') }} should award users each day. If you would like an element of
    chance in it, linking a loot table here is recommended.</p>

<p>The segment field defines which reward is set for what segment. The first segment is always on the right of the top
    middle of the wheel.</p>


@include('dailies._loot_select', ['loots' => $daily->rewards, 'showLootTables' => true, 'showRaffles' => true])
