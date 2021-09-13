<h1>Seed Settings</h1>

{!! Form::label('Waterings') !!}
{!! Form::number('waterings', $tag->getData()['waterings'], ['class' => 'form-control mb-1', 'min' => 1]) !!}

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


{!! Form::hidden('item', $item) !!}