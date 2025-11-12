<h3>Degradable</h3>

<p>Degradable items will lose durability over time. You can set the durability of the item and the rate at which it degrades based on activity.</p>
<p class="mb-0">If an activty does not have a set usage rate, the item will do one of the following:</p>
<ul>
    <li>Not degrade at all</li>
    <li>Be consumed after a single use</li>
</ul>
<p class="mb-0">You can choose which of these options you want to use for each activity.</p>
<p class="font-weight-bold">By default, the item will be consumed.</p>

<div class="form-group">
    {!! Form::label('uses', 'Uses') !!}
    {!! Form::number('uses', $tag->getData()['uses'], ['class' => 'form-control', 'placeholder' => 'Input Durability', 'min' => 1]) !!}
</div>

<h4>Usage Rates</h4>
<p>You can set the usage rate for each activity. The usage rate is the amount of durability lost per activity.</p>
<p>Usage rates are optional, if you do not set a usage rate for an activity, the item will not degrade when that activity is performed.</p>

@foreach ($tag->getEditData()['activities'] as $activity)
    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('usage_rate[' . $activity . ']', 'Usage Rate for ' . $activity, ['class' => 'control-label font-weight-bold']) !!}
            {!! Form::number('usage_rate[' . $activity . ']', $tag->getData()['usage_rate'][$activity], ['class' => 'form-control', 'placeholder' => 'Input Usage Rate', 'min' => 0]) !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::label('consumption_type[' . $activity . ']', 'Consumption Type', ['class' => 'control-label font-weight-bold']) !!}
            {!! Form::select('consumption_type[' . $activity . ']', ['consume' => 'Consume After Use', 'none' => 'Do Not Degrade'], $tag->getData()['consumption_type'][$activity], [
                'class' => 'form-control',
                'placeholder' => 'This option only applies if a usage rate is NOT set',
            ]) !!}
        </div>
    </div>
@endforeach
