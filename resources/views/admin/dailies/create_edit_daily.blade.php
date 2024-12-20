@extends('admin.layout')

@section('admin-title')
    {{ ucfirst(__('dailies.daily')) }}
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        ucfirst(__('dailies.daily')) => 'admin/data/dailies',
        ($daily->id ? 'Edit ' : 'Create ') . ucfirst(__('dailies.daily')) => $daily->id ? 'admin/data/dailies/edit/' . $daily->id : 'admin/data/dailies/create',
    ]) !!}

    <h1>
        {{ $daily->id ? 'Edit' : 'Create' }} {{ ucfirst(__('dailies.daily')) }}
        @if ($daily->id)
            ({!! $daily->displayName !!})
            <a href="#" class="btn btn-danger float-right delete-daily-button">Delete {{ ucfirst(__('dailies.daily')) }}</a>
        @endif
    </h1>

    {!! Form::open(['url' => $daily->id ? 'admin/data/dailies/edit/' . $daily->id : 'admin/data/dailies/create', 'files' => true]) !!}

    <h3>Basic Information</h3>
    <div class="row">
        <div class="form-group col-md-6">
            {!! Form::label('Name') !!}
            {!! Form::text('name', $daily->name, ['class' => 'form-control']) !!}
        </div>
        @if ($daily->id)
            <div class="col-md-6">
                {!! Form::label('type', 'Daily Type') !!}
                <div class="alert alert-info">
                    You cannot change the type of a daily after it has been created. Type: <strong>{{ $daily->type }}</strong>
                </div>
            </div>
        @else
            <div class="form-group col-md-6">
                {!! Form::label('type', 'Daily Type') !!} {!! add_help('Buttons are just one click to collect a reward. Wheels allow users to spin a wheel each day.') !!}
                {!! Form::select(
                    'type',
                    [
                        'Wheel' => 'Wheel',
                        'Button' => 'Button',
                        'Advent' => 'Advent',
                    ],
                    $daily ? $daily->type : null,
                    ['class' => 'form-control'],
                ) !!}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="form-group col">
            {!! Form::label('Fee (Optional)') !!} {!! add_help('Add a fee here if the user should pay for rolling the daily.') !!}
            {!! Form::text('fee', $daily->fee ?? 0, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col">
            {!! Form::label('currency_id', 'Currency (Optional)') !!} {!! add_help('Which currency the fee should be in. If left unselected, no fee will be applied.') !!}
            {!! Form::select('currency_id', $currencies, $daily->currency_id ?? null, ['class' => 'form-control', 'placeholder' => 'Select Currency']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label(__('dailies.daily') . ' Image (Optional)') !!} {!! add_help(
            'This image is used on the ' .
                __('dailies.daily') .
                ' index and on the ' .
                __('dailies.daily') .
                '
                            page as a header.',
        ) !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: None (Choose a standard size for all {{ __('dailies.daily') }} images). File type: png.</div>
        @if ($daily->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $daily->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('start_at', 'Start Time (Optional)') !!} {!! add_help('The ' . __('dailies.daily') . ' will cycle in at this date.') !!}
            {!! Form::text('start_at', $daily->start_at, ['class' => 'form-control datepicker']) !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::label('end_at', 'End Time (Optional)') !!} {!! add_help('The ' . __('dailies.daily') . ' will cycle out at this date.') !!}
            {!! Form::text('end_at', $daily->end_at, ['class' => 'form-control datepicker']) !!}
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-6">
            {!! Form::label('daily_timeframe', 'Daily Timeframe') !!} {!! add_help('This is the timeframe that the daily can
                                    be collected in. I.E. yearly will only allow one roll per year. Weekly allows one roll per week. Rollover will
                                    happen on UTC time.') !!}
            {!! Form::select('daily_timeframe', ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'], $daily ? $daily->daily_timeframe : 0, ['class' => 'form-control']) !!}

        </div>
        <div class="form-group col-md-6">
            {!! Form::label('prize_display', 'Prize Display') !!} {!! add_help('Decides what kind of information on the rewards for each segment should be shown on the daily page.') !!}
            {!! Form::select(
                'prize_display',
                [
                    'none' => 'Hidden Rewards',
                    'hidden' => 'Rewards Hidden Until Collected',
                    'all' => 'All Rewards Shown',
                ],
                $daily ? $daily->prize_display : 0,
                ['class' => 'form-control'],
            ) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
            'If turned
                                off,
                                the ' .
                __('dailies.daily') .
                ' will not be visible to regular users.',
        ) !!}
        {!! Form::checkbox('is_active', 1, $daily->id ? $daily->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    </div>

    @if ($daily->id)
        <hr />

        <div class="card mb-3">
            <div class="h3 card-header">
                Daily Settings
            </div>
            <div class="card-body">
                @include('admin.dailies.types._' . $daily->type . '_daily')
            </div>
        </div>

        <div class="card mb-3">
            <h3 class="card-header">Rewards</h3>
            <div class="card-body">
                <p>
                    Please add what reward the {{ __('dailies.daily') }} should award users each day. If you would like an element of
                    chance in it, linking a loot table here is recommended.
                </p>
                <p>
                    The segment field defines which reward is set for what segment. The first segment is always on the right of the top
                    middle of the wheel.
                </p>
                @include('dailies._loot_select', ['loots' => $daily->rewards, 'showLootTables' => true, 'showRaffles' => true])
            </div>
        </div>
    @endif

    <div class="text-right">
        {!! Form::submit($daily->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('dailies._loot_select_row', [
        'items' => $items,
        'currencies' => $currencies,
        'tables' => $tables,
        'raffles' => $raffles,
        'showLootTables' => true,
        'showRaffles' => true,
    ])
@endsection
@section('scripts')
    @parent
    @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
    @include('widgets._datetimepicker_js')
    <script>
        $(document).ready(function() {
            $('#is_timed_daily').change(function() {
                console.log('changed');
                if ($(this).is(':checked')) {
                    $('.daily-timed-quantity').removeClass('hide');
                } else {
                    $('.daily-timed-quantity').addClass('hide');
                }
            });
            $('.delete-daily-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/dailies/delete') }}/{{ $daily->id }}", 'Delete ' + "{{ ucfirst(__('dailies.daily')) }}");
            });
            $('.add-daily-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/dailies/daily') }}/{{ $daily->id }}", 'Add Stock');
            });
        });
    </script>
@endsection
