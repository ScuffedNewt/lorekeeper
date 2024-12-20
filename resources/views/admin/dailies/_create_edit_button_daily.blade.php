<h3>Options</h3>

<div class="row p-4">
    <div class="form-group col">
        {!! Form::select('daily_timeframe', ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'], $daily ? $daily->daily_timeframe : 0, ['class' => 'form-control stock-field', 'data-name' => 'daily_timeframe']) !!}
        {!! Form::label('daily_timeframe', 'Daily Timeframe') !!} {!! add_help('This is the timeframe that the daily can be collected in. I.E. yearly will only allow one roll per year. Weekly allows one roll per week. Rollover will happen on UTC time.') !!}
    </div>
    <div class="form-group col">
        {!! Form::select('progress_display', ['none' => 'None', 'hidden' => 'Rewards hidden until collected', 'all' => 'All rewards shown'], $daily ? $daily->progress_display : 0, [
            'class' => 'form-control stock-field',
            'data-name' => 'progress_display',
        ]) !!}
        {!! Form::label('progress_display', 'Progress Display') !!} {!! add_help('Decides what kind of information on the rewards for each step should be shown on the daily page.') !!}
    </div>
    <div class="form-group col">
        {!! Form::checkbox('is_loop', 1, $daily->id ? $daily->is_loop : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_loop', 'Set Loop', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
            'If turned off, each of
                the ' .
                __('dailies.daily') .
                ' rewards will only be able to be claimed once.',
        ) !!}
    </div>
    <div class="form-group col">
        {!! Form::checkbox('is_streak', 1, $daily->id ? $daily->is_streak : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_streak', 'Is Streak', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, should the user miss a day of claiming, the rewards start over from day 1.') !!}
    </div>
    <div class="form-group col">
        {!! Form::checkbox('is_active', 1, $daily->id ? $daily->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
            'If turned off,
                the ' .
                __('dailies.daily') .
                ' will not be visible to regular users.',
        ) !!}
    </div>

</div>

<div class="pl-4">
    <div class="form-group">
        {!! Form::checkbox('is_timed_daily', 1, $daily->is_timed_daily ?? 0, ['class' => 'form-check-input daily-timed daily-toggle daily-field', 'id' => 'is_timed_daily']) !!}
        {!! Form::label('is_timed_daily', 'Set Timed ' . __('dailies.daily'), ['class' => 'form-check-label ml-3']) !!} {!! add_help('Sets the ' . __('dailies.daily') . ' as timed between the chosen dates.') !!}
    </div>
    <div class="daily-timed-quantity {{ $daily->is_timed_daily ? '' : 'hide' }}">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('start_at', 'Start Time') !!} {!! add_help('The ' . __('dailies.daily') . ' will cycle in at this date.') !!}
                    {!! Form::text('start_at', $daily->start_at, ['class' => 'form-control', 'id' => 'datepicker2']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('end_at', 'End Time') !!} {!! add_help('The ' . __('dailies.daily') . ' will cycle out at this date.') !!}
                    {!! Form::text('end_at', $daily->end_at, ['class' => 'form-control', 'id' => 'datepicker3']) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<h3>Images</h3>

<div class="form-group">
    {!! Form::label(__('dailies.daily') . ' Image (Optional)') !!} {!! add_help(
        'This image is used on the ' .
            __('dailies.daily') .
            ' index and on the ' .
            __('dailies.daily') .
            '
        page as a header.',
    ) !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: None (Choose a standard size for all {{ __('dailies.daily') }} images). File type: png.</div>
    @if ($daily->has_image)
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>
<div class="form-group">
    {!! Form::label('Button Image (Optional)') !!} {!! add_help('This image is used for the button instead of the generic Collect Reward button.') !!}
    <div>{!! Form::file('button_image') !!}</div>
    <div class="text-muted">Recommended size: 200x200px or something small. File type: png.</div>
    @if ($daily->has_button_image)
        <div class="form-check">
            {!! Form::checkbox('remove_button_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_button_image', 'Remove current button image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>


<h3>Rewards</h3>
<p>Please add what reward the {{ __('dailies.daily') }} should award users each day. If you would like an element of chance in it, linking a loot table here is recommended.</p>

<p>The step field is needed for progressable {{ __('dailies.dailies') }}. It defines what rewards the user can get at each step as. Once the end of the steps is reached, the rewards start over from step 1. </p>
<b>If you have no need for progression, simply always leave it at step 1. If you want to set a default reward that is picked for all steps with no reward set, you may specify it as step 0.</b> Beware that the default reward will not be shown on the
progress.



@include('dailies._loot_select', ['loots' => $daily->rewards, 'showLootTables' => true, 'showRaffles' => true])
