<h1>Fertiliser Settings</h1>

{!! Form::label('quality') !!}
{!! Form::number('quality', $tag->getData()['quality'], ['class' => 'form-control mb-1', 'min' => 1]) !!}