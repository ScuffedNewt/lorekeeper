@php
    $weathers = \App\Models\Weather\Weather::orderBy('name')->pluck('name', 'id');
    $objectWeather = \App\Models\Weather\ObjectWeather::where('object_id', $object->id)
        ->where('object_model', get_class($object))
        ->first();
    $resetPeriods = [null => 'None', 'Hour' => 'Hourly', 'Day' => 'Daily', 'Week' => 'Weekly', 'Month' => 'Month', 'Year' => 'Year'];
@endphp

<div class="card p-4 mb-2 mt-2" id="weather-card">
    <h3>Weather</h3>

    <p>
        Weather can be added to this object to give it a little extra flavor. You can add multiple weather types to a single object, and have multiple be active!
        <br/>You can also optionally set a reset time for the weather, which will cause the weather to change after the set amount of time has passed.
        <br />This should be treated as: 
        <ul class="mb-0">
            <li>The current weather for the object. Ex. if you have a prompt that focuses on character's reaction to the weather.</li>
            <li>A list of possible weather types that can occur at this object. Ex. Listing a location's typical weather.</li>
            <li>During what weather types this object is accessible. Ex. if a prompt only becomes available during "rain".</li>
        </ul>
    </p>
    <p>
        If you want this object to only be accessible during certain weathers, simply enable the "Hidden" option. This means the object will only be visible if <em>The site's weather</em> matches the set weather.
        <br />To make more objects hideable aside from the default provided, refer to the scopes in the <code>PromptController::getPrompts</code> function.
    </p>
    <p><strong>By default, the current season's weather will also be included in the available weather for this object, UNLESS it is a hidden object.</strong></p>
    <div>
        <div class="row">
            <div class="col-md form-group">
                {!! Form::label('Use Season Weather?') !!} {!! add_help('If checked, the current season\'s weather will be included in the available weather for this object UNLESS this is a hidden object.') !!}
                {!! Form::checkbox('use_season_weather', 1, $objectWeather?->use_season_weather, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'useSeasonWeather']) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('Hidden') !!} {!! add_help('If checked, this object will only be visible if the site\'s weather matches any of the set weather.') !!}
                {!! Form::checkbox('is_hidden', 1, $objectWeather?->is_hidden, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'hidden']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('Reset Period') !!} {!! add_help('If set, the weather will reset to a new random weather type after the set period of time has passed.') !!}
            {!! Form::select('reset_period', $resetPeriods, $objectWeather?->reset_period, ['class' => 'form-control', 'placeholder' => 'Reset Period', 'id' => 'resetPeriod']) !!}
        </div>
        <div class="row">
            <div class="col-md form-group">
                {!! Form::label('Minimum Weather Selected') !!} {!! add_help('The minimum number of weather types that will be selected for this object if the reset period is set.') !!}
                {!! Form::number('min_selected_weather', $objectWeather ? $objectWeather->data['min_selected_weather'] ?? 1 : 1, ['class' => 'form-control', 'placeholder' => 'Minimum Weather Selected', 'id' => 'minSelectedWeather']) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('Maximum Weather Selected') !!} {!! add_help('The maximum number of weather types that will be selected for this object if the reset period is set.') !!}
                {!! Form::number('max_selected_weather', $objectWeather ? $objectWeather->data['max_selected_weather'] ?? 1 : 1, ['class' => 'form-control', 'placeholder' => 'Maximum Weather Selected', 'id' => 'maxSelectedWeather']) !!}
            </div>
        </div>
        @if ($objectWeather)
            <h5>Weather for {!! $objectWeather->object->displayName !!}</h5>
            @if ($objectWeather->use_season_weather && !$objectWeather->is_hidden)
                <small>
                    <div class="alert alert-info mb-1 p-1">
                        Note: some of the available weather for this object are the current season's weather.
                    </div>
                </small>
            @endif
            <p class="mb-2">Current Available Weather: {!! $objectWeather->getWeatherMessage() !!}</p>
        @endif
        <div class="card mb-3">
            <div class="card-body pb-0 row no-gutters" style="border-bottom: 1px solid #ccc;">
                <div class="col-md-4">
                    {!! Form::label('Weather') !!}
                </div>
                <div class="col-md-4">
                    {!! Form::label('Weight') !!} {!! add_help('Note: if \'Hidden\' is enabled, this is not used. Instead, the object will only be visible if the site\'s weather matches any of the set weather.') !!}
                </div>
                <div class="col-md-2">
                    {!! Form::label('Active?') !!} {!! add_help('Note: if \'Hidden\' is enabled, this toggle is not used. Instead, the object will only be visible if the site\'s weather matches any of the set weather.') !!}
                </div>
                <div class="col-md-1">
                    {!! Form::label('Chance') !!}
                </div>
                <div class="col-md-1">
                    {!! Form::label('Remove') !!}
                </div>
            </div>
            <hr class="mb-2 mt-1">
            <div class="card-body pb-0" id="weathers">
                @foreach ($objectWeather?->weathers ?? [] as $id => $weight)
                    <div class="row">
                        <div class="col-md-4 form-group">
                            {!! Form::select('weather_ids[]', $weathers, $id, ['class' => 'form-control weather-selectize', 'placeholder' => 'Select Weather']) !!}
                        </div>
                        <div class="col-md-4 form-group">
                            {!! Form::number('weight[]', $weight, ['class' => 'form-control weight', 'placeholder' => 'Weight']) !!}
                        </div>
                        <div class="col-md-2 form-group d-flex align-items-center">
                            {!! Form::checkbox('active[]', 1, $objectWeather->isWeatherActive($id), ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                        </div>
                        <div class="col-md-1 d-flex align-items-center">
                            <div class="chance"></div>
                        </div>
                        <div class="col-md-1 d-flex align-items-center p-3">
                            <div class="btn btn-danger original remove-weather">X</div>
                        </div>
                    </div>
                @endforeach
            </div>
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
            {!! Form::select('weather_ids[]', $weathers, null, ['class' => 'form-control select', 'placeholder' => 'Select Weather']) !!}
        </div>
        <div class="col-md-4 form-group">
            {!! Form::number('weight[]', null, ['class' => 'form-control weight', 'placeholder' => 'Weight']) !!}
        </div>
        <div class="col-md-2 form-group">
            {!! Form::checkbox('active[]', 1, null, ['class' => 'form-check-input']) !!}
        </div>
        <div class="col-md-1">
            <div class="chance"></div>
        </div>
        <div class="col-md-1">
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

        $('.original.remove-weather').on('click', function() {
            $(this).parent().parent().parent().remove();
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
            // tooltip the data-toggle elements
            $clone.find('[data-toggle="toggle"]').tooltip({
                html: true
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
                    }).get(),
                    min_selected_weather: $('#minSelectedWeather').val(),
                    max_selected_weather: $('#maxSelectedWeather').val(),
                    use_season_weather: $('#useSeasonWeather').is(':checked') ? 1 : 0,
                    is_hidden: $('#hidden').is(':checked') ? 1 : 0
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
