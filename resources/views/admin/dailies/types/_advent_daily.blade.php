<div class="alert alert-warning">
    <strong>Advent Daily</strong> is a type of daily that is a countdown to a specific date, therefore, a "Start At" date must be set.
</div>

<div class="form-group">
    {!! Form::label('data[set_days]', 'Set Days?', ['class' => 'form-check-label']) !!}
    <p>
        When turned on, if a user misses a day they cannot receive the reward for that day and must continue from the current day of the advent.
        <br />
        For example, if a user forgets to claim the day 5 reward, they will not be able to claim it on day 6, they will have to claim day 6's reward instead.
        <br />
        If this option is off, they can continue to the next consecutive day but will no longer be able to complete the advent (unless some buffer is added to the end date).
    </p>
    {!! Form::checkbox('data[set_days]', 1, $daily->data['set_days'] ?? 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
</div>
