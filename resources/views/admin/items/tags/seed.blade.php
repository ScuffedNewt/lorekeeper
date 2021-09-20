<h1>Seed Settings</h1>

{!! Form::label('Waterings') !!}
{!! Form::number('waterings', $tag->getData()['waterings'], ['class' => 'form-control mb-1 waterings', 'min' => 1]) !!}

{!! Form::label('Max Quantity') !!} {!! add_help('Input the maximum amount of crop that can be grown from one seed.') !!}
{!! Form::number('quantity', $tag->getData()['quantity'], ['class' => 'form-control mb-1', 'min' => 1]) !!}

{!! Form::label('Final Plant') !!}
{!! Form::select('plant_id', $items, $tag->getData()['plant_id'], ['class' => 'form-control mb-2']) !!}

<h5>Seasons (Optional)</h5>
<p>If you want to limit the growth of this seed to a certain season, select the months it should be grown between below.</p>
<p>The user will be able to grow from the start month through till the end of the end month, ex: if the start month is April and the end month is October, the user will be able to grow from April to October.</p>

<div class="row">
    <div class="col-md-6">
        {!! Form::label('Start Month') !!}
        {!! Form::select('start_month', $months, $tag->getData()['start_month'], ['class' => 'form-control mb-2', 'placeholder' => 'Select Month']) !!}
    </div>
    <div class="col-md-6">
        {!! Form::label('End Month') !!}
        {!! Form::select('end_month', $months, $tag->getData()['end_month'], ['class' => 'form-control mb-2', 'placeholder' => 'Select Month']) !!}
    </div>
</div>

<h5>Stages (Optional)</h5>
<p>If you want unique seed stages. If a seed has no stages it'll default to the seed.png in the public folder.</p>
<p>If you want the image to be at final stage / before claiming, set it equal to the number of waterings.</p>
<div class="form-group">
    {!! Form::label('Stages') !!}
    <div id="stageList">
        @if($tag->getData()['stages'])
        @foreach($tag->getData()['stages'] as $stage)
            <div class="d-flex mb-2">
                {!! Form::file('stage_image[]') !!} {!! add_help('Leave empty to keep old image.') !!}
                {!! Form::number('stage_number[]', $stage, ['class' => 'form-control mr-2', 'placeholder' => 'When Does the Stage Appear?']) !!}
                <a href="#" class="remove-stage btn btn-danger mb-2">×</a>
            </div>
        @endforeach
        @endif
    </div>
    <div><a href="#" class="btn btn-primary" id="add-stage">Add Stage</a></div>
    <div class="stage-row hide mb-2">
        {!! Form::file('stage_image[]') !!}
        {!! Form::number('stage_number[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'When Does the Stage Appear? EX at what watering.', 'min' => 0]) !!}
        <a href="#" class="remove-stage btn btn-danger mb-2">×</a>
    </div>
</div>

{!! Form::hidden('item', $item) !!}
<script>
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