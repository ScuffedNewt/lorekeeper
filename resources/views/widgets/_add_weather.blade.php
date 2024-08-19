@php
    $weathers = \App\Models\Weather\Weather::orderBy('name')->pluck('name', 'id');
    $objectWeather = \App\Models\Weather\ObjectWeather::where('object_id', $object->id)
        ->where('object_model', get_class($object))
        ->first();
    $resetPeriods = [null => 'None', 'Hour' => 'Hourly', 'Day' => 'Daily', 'Week' => 'Weekly', 'Month' => 'Month', 'Year' => 'Year'];
@endphp

<div class="card p-4 mb-2 mt-2" id="weather-card">
    <h3>Weather</h3>

    <p>Weather can be added to this object to give it a little extra flavor. You can add multiple weather types to a single object, and have multiple be active!</p>
    <p>You can also optionally set a reset time for the weather, which will cause the weather to change after the set amount of time has passed.</p>

    <div>
        <div class="form-group">
            {!! Form::label('Reset Period') !!} {!! add_help('If set, the weather will reset to a new random weather type after the set period of time has passed.') !!}
            {!! Form::select('reset_period', $resetPeriods, null, ['class' => 'form-control', 'placeholder' => 'Reset Period', 'id' => 'resetPeriod']) !!}
        </div>
        <div id="weathers">
            @if ($objectWeather)
                <h5>Weather for {!! $objectWeather->object->displayName !!}</h5>
                Current Weather: {!! $objectWeather->getWeatherMessage() !!}
                @foreach ($objectWeather->weathers as $id => $weight)
                    <div class="row">
                        <div class="col-md-4 form-group">
                            {!! Form::label('Weather') !!}
                            {!! Form::select('weather_ids[]', $weathers, $id, ['class' => 'form-control weather-selectize', 'placeholder' => 'Select Weather']) !!}
                        </div>
                        <div class="col-md-4 form-group">
                            {!! Form::label('Weight') !!}
                            {!! Form::number('weight[]', $weight, ['class' => 'form-control weight', 'placeholder' => 'Weight']) !!}
                        </div>
                        <div class="col-md-2 form-group d-flex align-items-center pt-3">
                            {!! Form::checkbox('active[]', 1, $objectWeather->isWeatherActive($id), ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                            {!! Form::label('active[]', 'Active?', ['class' => 'form-check-label ml-3']) !!}
                        </div>
                        <div class="col-md-1 d-flex align-items-center">
                            <div class="chance"></div>
                        </div>
                        <div class="col-md-1 d-flex align-items-center p-3">
                            <div class="btn btn-danger original remove-weather">X</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="btn btn-secondary" id="add-weather">Add Weather</div>
        <div class="btn btn-primary float-right" id="submit-weather">{{ $objectWeather ? 'Edit' : 'Create' }} Weather</div>
        @if ($objectWeather)
            <div class="btn btn-danger float-right mr-2" id="delete-weather">Delete Weather</div>
        @endif
    </div>
</div>

<div class="hide weather-row">
    <div class="row">
        <div class="col-md-4 form-group">
            {!! Form::label('Weather') !!}
            {!! Form::select('weather_ids[]', $weathers, null, ['class' => 'form-control select', 'placeholder' => 'Select Weather']) !!}
        </div>
        <div class="col-md-4 form-group">
            {!! Form::label('Weight') !!}
            {!! Form::number('weight[]', null, ['class' => 'form-control weight', 'placeholder' => 'Weight']) !!}
        </div>
        <div class="col-md-2 form-group d-flex align-items-center pt-3">
            {!! Form::checkbox('active[]', 1, null, ['class' => 'form-check-input']) !!}
            {!! Form::label('active[]', 'Active?', ['class' => 'form-check-label ml-3']) !!}
        </div>
        <div class="col-md-1 d-flex align-items-center">
            <div class="chance"></div>
        </div>
        <div class="col-md-1 d-flex align-items-center p-3">
            <div class="btn btn-danger remove-weather">X</div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.weather-selectize').selectize();

        function updateChance() {
            var total = 0;
            $('.weight').each(function() {
                if ($(this).val() == '') {
                    $(this).val(0);
                }
                total += parseInt($(this).val());
            });

            $('.weight').each(function() {
                var chance = Math.round((parseInt($(this).val()) / total) * 100);
                $(this).parent().parent().find('.chance').text(chance + '%');
            });
        }
        updateChance();

        $('.weight').on('change', function() {
            updateChance();
        });

        $('.original .remove-weather').on('click', function() {
            $(this).parent().parent().remove();
        });

        // add weather
        $('#add-weather').on('click', function(e) {
            e.preventDefault();
            var $clone = $('.weather-row').clone();
            $('#weathers').append($clone);

            $clone.removeClass('hide weather-row');
            $clone.find('.form-check-input').attr('data-toggle', 'toggle').bootstrapToggle();
            $clone.find('select').selectize();
            $clone.find('.weight').on('change', function() {
                updateChance();
            });
            $clone.find('.remove-weather').on('click', function() {
                $clone.remove();
            });
        });

        // delete weather
        @if ($objectWeather)
            $('#delete-weather').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/weather/objects/delete/' . $objectWeather->id) }}", "Delete Weather");
            });
        @endif

        // ajax on add weather
        $('#submit-weather').on('click', function(e) {
            e.preventDefault();
            var $weather = $('.weather');
            var $submit = $weather.find('#submit-weather');
            var $error = $weather.find('.error');
            var $success = $weather.find('.success');

            $submit.addClass('disabled');
            $error.addClass('d-none');
            $success.addClass('d-none');

            console.log($('#resetPeriod').val());

            $.ajax({
                url: "{{ url('admin/weather/objects') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    objectWeather: '{{ $objectWeather ? $objectWeather->id : null }}',
                    object_model: '{{ urlencode(get_class($object)) }}',
                    object_id: '{{ $object->id }}',
                    weather_ids: $('#weathers').find('select').map(function() {
                        return $(this).val();
                    }).get(),
                    reset_period: $('#resetPeriod').val(),
                    active: $('#weathers').find('.form-check-input').map(function() {
                        return $(this).is(':checked') ? 1 : 0;
                    }).get(),
                    weight: $('#weathers').find('.weight').map(function() {
                        return $(this).val();
                    }).get()
                },
                success: function(data) {
                    location.reload();
                },
                error: function(data) {
                    location.reload();
                }
            });
        });
    });
</script>
