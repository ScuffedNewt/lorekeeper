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

    <h1>{{ $daily->id ? 'Edit' : 'Create' }} {{ ucfirst(__('dailies.daily')) }}
        @if ($daily->id)
            ({!! $daily->displayName !!})
            <a href="#" class="btn btn-danger float-right delete-daily-button">Delete {{ ucfirst(__('dailies.daily')) }}</a>
        @endif
    </h1>

    {!! Form::open(['url' => $daily->id ? 'admin/data/dailies/edit/' . $daily->id : 'admin/data/dailies/create', 'files' => true]) !!}

    <h3>Basic Information</h3>
    <div class="row">
        <div class="form-group col">
            {!! Form::label('Name') !!}
            {!! Form::text('name', $daily->name, ['class' => 'form-control']) !!}

        </div>
        @if (!$daily->id)
            <div class="form-group col">
                {!! Form::label('type', 'Daily Type') !!} {!! add_help('Buttons are just one click to collect a reward. Wheels allow users to spin a wheel each day.') !!}
                {!! Form::select('type', ['Button' => 'Button', 'Wheel' => 'Wheel'], $daily ? $daily->type : null, ['class' => 'form-control']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $daily->description, ['class' => 'form-control wysiwyg']) !!}
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

    @if ($daily->id)
        @if ($daily->type == 'Button')
            @include('admin.dailies._create_edit_button_daily')
        @elseif($daily->type == 'Wheel')
            @include('admin.dailies._create_edit_wheel_daily', ['wheel' => $daily->wheel])
        @endif
    @endif

    <div class="text-right">
        {!! Form::submit($daily->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}
    @include('dailies._loot_select_row', ['items' => $items, 'currencies' => $currencies, 'tables' => $tables, 'raffles' => $raffles, 'showLootTables' => true, 'showRaffles' => true])

@endsection

@section('scripts')
    @parent
    @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])

    <script>
        $('#is_timed_daily').change(function() {
            if ($(this).is(':checked')) {
                $('.daily-timed-quantity').removeClass('hide');
            } else {
                $('.daily-timed-quantity').addClass('hide');
            }
        });

        $("#datepicker2").datetimepicker({
            dateFormat: "yy-mm-dd",
            timeFormat: 'HH:mm:ss',
        });

        $("#datepicker3").datetimepicker({
            dateFormat: "yy-mm-dd",
            timeFormat: 'HH:mm:ss',
        });


        $(document).ready(function() {


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
