<h1>Seed Settings</h1>

{!! Form::label('Waterings') !!}
{!! Form::number('waterings', $tag->getData()['waterings'], ['class' => 'form-control mb-1', 'min' => 1]) !!}

{!! Form::label('Final Plant') !!}
{!! Form::select('plant_id', $items, $tag->getData()['plant_id'], ['class' => 'form-control mb-2']) !!}

{!! Form::hidden('item', $item) !!}