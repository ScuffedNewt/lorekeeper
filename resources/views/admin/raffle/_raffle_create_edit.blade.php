@if(!$raffle->id)
    <p>
        Enter basic information about this raffle. Tickets can be added after the raffle is created.
    </p>
@endif
{!! Form::open(['url' => 'admin/raffles/edit/raffle/'.($raffle->id ? : '')]) !!}
    <div class="form-group">
        {!! Form::label('name', 'Raffle Name') !!} {!! add_help('This is the name of the raffle. Naming it something after what is being raffled is suggested (does not have to be unique).') !!}
        {!! Form::text('name', $raffle->name, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('winner_count', 'Number of Winners to Draw') !!}
        {!! Form::text('winner_count', $raffle->winner_count, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('group_id', 'Raffle Group') !!} {!! add_help('Raffle groups must be created before you can select them here.') !!}
        {!! Form::select('group_id', $groups, $raffle->group_id, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('order', 'Raffle Order') !!} {!! add_help('Enter a number. If a group of raffles is rolled, raffles will be drawn in ascending order.') !!}
        {!! Form::text('order', $raffle->order ? : 0, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        <label class="control-label">
            {!! Form::checkbox('is_active', 1, $raffle->is_active, ['class' => 'form-check-input mr-2', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_displayed', 'Active (visible to users)', ['class' => 'form-check-label ml-3']) !!}
        </label>
    </div>
    <div class="form-group">
        <label class="control-label">
            {!! Form::checkbox('allow_entry', 1, $raffle->allow_entry, ['class' => 'form-check-input mr-2', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('allow_entry', 'Allow users to enter?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Allows users to enter themselves into the raffle.') !!}
        </label>
    </div>
    <div class="form-group">
        <label class="control-label">
            {!! Form::checkbox('unordered', 1, $raffle->unordered, ['class' => 'form-check-input mr-2', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('unordered', 'Unordered Results?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If there are less entrants than winners, the numbers will be random instead of 1 - X.') !!}
        </label>
    </div>
    <div class="text-right">
        {!! Form::submit('Confirm', ['class' => 'btn btn-primary']) !!}
    </div>
{!! Form::close() !!}