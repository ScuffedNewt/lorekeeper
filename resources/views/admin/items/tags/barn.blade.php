{!! Form::label('Feedings') !!}
{!! Form::number('feedings', $tag->getData()['feedings'], ['class' => 'form-control mb-1', 'min' => 1]) !!}

<h3>Rewards</h3>
@include('widgets._loot_select', ['loots' => $tag->getData()['rewards'], 'showLootTables' => true, 'showRaffles' => true])


{!! Form::hidden('item', $item) !!}