<h2>Seed Settings</h2>

{!! Form::label('Waterings') !!}
{!! Form::number('actions', $tag->getData()['actions'], ['class' => 'form-control mb-1 waterings', 'min' => 1]) !!}

{!! Form::label('Minimum Fertiliser Quality (Optional)') !!}
{!! Form::number('quality', $tag->getData()['quality'], ['class' => 'form-control mb-1', 'min' => 0]) !!}

<hr>
<h2 class="h5">Definite Rewards</h2>
<p>These rewards will be earned upon success. If you wish to have potential for, say, better quality produce, use a loot table.</p>
@include('widgets._loot_select', ['loots' => $tag->getData()['rewards'], 'showLootTables' => true, 'showRaffles' => true])
<hr>

<h2 class="h5">Potential Rewards (Optional)</h2>
<p>This reward has the potential to be obtained if the bonus chance below succeeds. Only items are only supported currently.</p>
@include('widgets._garden_loot_select', ['loots' => $tag->getData()['potential_rewards'], 'showLootTables' => true, 'showRaffles' => true])

{!! Form::label('Bonus Chance Percentage') !!}
{!! Form::number('bonus_chance', isset($tag->getData()['bonus_chance']) ? $tag->getData()['bonus_chance'] : 0, ['class' => 'form-control mb-1', 'min' => 0, 'max' => 100]) !!}


<hr>
<h2 class="h5">Seasons (Optional)</h2>
<p>If you want to limit the growth of this seed to a certain season, select the months it should be grown between below.</p>
<p>The user will be able to grow from the start month through till the end of the end month, ex: if the start month is April and the end month is October, the user will be able to be planted from April to October.
    <br> Users will be able to harvest the seed at any time, but will only be able to plant it during the months selected.
    <br><b>You need to set both even if you wish to only have one month, e.g. set both to "January", otherwise you may experience unexpected results due to how date comparison works.
        If both are not set the date checks will not run.
        <br>By default if start month is set and not end month, the end month will be set to be the same as the start month.
    </b>
</p>
<p>Users cannot see seeds that are "out of season" as options on the garden page.</p>

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

<hr>
<h2 class="h5">Stages (Optional)</h2>
<p>If you want unique seed stages. If a seed has no stages it'll default to the seed.png in the public/images/garden folder.</p>
<p>If you want the image to be at final stage / before claiming, set it equal to the number of waterings required to complete the growing.</p>
<p><b>If stages exist, the seed will find the "highest" tier stage to display as the image.</b></p>
<div class="form-group">
    <div id="stageList">
        @if($tag->getData()['stages'])
            <p>Leave image form blank to keep old image.</p>
            @foreach($tag->getData()['stages'] as $stage)
                <div class="d-flex mb-2">
                    <img src="{{ asset('images/data/seed-stages/'.$tag->id.'-'.$stage.'-image.png') }}" alt="Stage {{ $stage }}" class="mr-2" style="height: 50px;">
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
        {!! Form::number('stage_number[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'When does the stage appear? E.g. stage == number of completed waterings.', 'min' => 0]) !!}
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
