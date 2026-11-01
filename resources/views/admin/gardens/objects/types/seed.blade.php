@php
    $months = [
        '1' => 'January',
        '2' => 'February',
        '3' => 'March',
        '4' => 'April',
        '5' => 'May',
        '6' => 'June',
        '7' => 'July',
        '8' => 'August',
        '9' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December',
    ];
@endphp

<div class="card mb-3">
    <div class="card-header h3">Options</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md form-group">
                {!! Form::label('Number of Waterings?') !!} {!! add_help('The number of times this seed needs to be watered before it can be harvested.') !!}
                {!! Form::number('interactions', isset($object->data['interactions']) ? $object->data['interactions'] : 1, ['class' => 'form-control']) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('Growth Time (Hours)') !!} {!! add_help('The number of hours it takes for this seed to grow / be able to be watered again.') !!}
                {!! Form::number('growth_time', isset($object) ? $object->data['growth_time'] : 24, ['class' => 'form-control', 'min' => 1]) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('Perish Grace Period (Hours)') !!} {!! add_help('In gardens or plots where perishing is enabled, the seed dies this many hours after a watering or harvest becomes ready.') !!}
                {!! Form::number('perish_time', isset($object) ? $object->data['perish_time'] ?? 24 : 24, ['class' => 'form-control', 'min' => 1]) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('Minimum Fertiliser Quality (Optional)') !!} {!! add_help('The minimum quality of fertiliser required to grow this seed. Leave at 0 for no requirement.') !!}
                {!! Form::number('quality', isset($object) ? $object->data['quality'] : 0, ['class' => 'form-control mb-1', 'min' => 0]) !!}
            </div>
        </div>

        <hr />

        @include('admin.gardens.objects._tool_requirements', ['object' => $object ?? null])

        <hr />

        <h2 class="h5">Seasons (Optional)</h2>
        <p>If you want to limit the growth of this seed to a certain season, select the months it should be grown between below.</p>
        <p>The user will be able to grow from the start month through till the end of the end month, ex: if the start month is April and the end month is October, the user will be able to be planted from April to October.
            <br> Users will be able to harvest the seed at any time, but will only be able to plant it during the months selected.
            <br>
            <b>
                <br>By default if start month is set and not end month, the end month will be set to be the same as the start month.
            </b>
        </p>
        <p>Users cannot see seeds that are "out of season" as options on the garden page.</p>

        <div class="alert alert-info">
            If you have the weather extension installed, you can use the seasons from that instead.
        </div>

        <div class="row">
            <div class="col-md-6">
                {!! Form::label('Start Month') !!}
                {!! Form::select('start_month', $months, isset($object) ? $object->data['start_month'] : null, ['class' => 'form-control mb-2', 'placeholder' => 'Select Month']) !!}
            </div>
            <div class="col-md-6">
                {!! Form::label('End Month') !!}
                {!! Form::select('end_month', $months, isset($object) ? $object->data['end_month'] : null, ['class' => 'form-control mb-2', 'placeholder' => 'Select Month']) !!}
            </div>
        </div>

        <hr />

        <h2 class="h5">Stages (Optional)</h2>
        <p>If you want unique seed stages. If a seed has no stages it'll default to the seed.png in the public/images/garden folder.</p>
        <p>If you want the image to be at final stage / before claiming, set it equal to the number of waterings required to complete the growing.</p>
        <p><b>If stages exist, the seed will find the "highest" tier stage to display as the image.</b></p>
        <div class="form-group">
            <div id="stageList">
                @if (isset($object) && isset($object->data['stages']))
                    <p>Leave image form blank to keep old image.</p>
                    @foreach ($object->data['stages'] as $stage)
                        <div class="d-flex mb-2 align-items-center">
                            <img src="{{ $object->stageImage($stage) }}" alt="Stage {{ $stage }}" class="mr-2" style="height: 50px;">
                            <div class="custom-file">
                                {!! Form::label('stage_image[]', 'Choose file...', ['class' => 'custom-file-label']) !!}
                                {!! Form::file('stage_image[]', ['class' => 'custom-file-input']) !!}
                            </div>
                            {!! Form::number('stage_number[]', $stage, ['class' => 'form-control mx-2', 'placeholder' => 'When Does the Stage Appear?']) !!}
                            <a href="#" class="remove-stage btn btn-danger">×</a>
                        </div>
                    @endforeach
                @endif
            </div>
            <div><a href="#" class="btn btn-primary" id="add-stage">Add Stage</a></div>
            <div class="stage-row hide mb-2 align-items-center">
                <div class="custom-file">
                    {!! Form::label('stage_image[]', 'Choose file...', ['class' => 'custom-file-label']) !!}
                    {!! Form::file('stage_image[]', ['class' => 'custom-file-input']) !!}
                </div>
                {!! Form::number('stage_number[]', null, ['class' => 'form-control mx-2', 'placeholder' => 'When does the stage appear? E.g. stage == number of completed waterings.', 'min' => 0]) !!}
                <a href="#" class="remove-stage btn btn-danger">×</a>
            </div>
        </div>

        <hr />

        <div class="card mb-3">
            <div class="card-header h5">Definite Rewards</div>
            <div class="card-body">
                <p>These rewards will be earned upon success. If you wish to have potential for, say, better quality produce, use a loot table.</p>
                <strong>Some ideas with loot tables:</strong>
                <ul class="mb-0">
                    <li>"Wild" seeds that can grow a random plant.</li>
                    <li>Chance for higher quality produce.</li>
                </ul>
                @include('widgets._loot_select', ['loots' => isset($object) ? $object->rewards : [], 'showLootTables' => true, 'showRaffles' => true])
            </div>
        </div>

        @include('admin.gardens.objects._potential_rewards', ['object' => $object ?? null])

    </div>
</div>

@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true, 'prefix' => 'potential_'])
@include('js._loot_js', ['prefix' => 'tool_'])

<script>
    $('[data-toggle="tooltip"]').tooltip({
        html: true
    });
    $(document).ready(function() {
        $('.original.stage-select').selectize();
        $('#add-stage').on('click', function(e) {
            e.preventDefault();
            addStageRow();
        });
        $('.remove-stage').on('click', function(e) {
            e.preventDefault();
            removeStageRow($(this));
        })

        function addStageRow() {
            var $clone = $('.stage-row').clone();
            $('#stageList').append($clone);
            $clone.removeClass('hide stage-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-stage').on('click', function(e) {
                e.preventDefault();
                removeStageRow($(this));
            })
            $clone.find('.stage-select').selectize();
        }

        function removeStageRow($trigger) {
            $trigger.parent().remove();
        }
    });
</script>
