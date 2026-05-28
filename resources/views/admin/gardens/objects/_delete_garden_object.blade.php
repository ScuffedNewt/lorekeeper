{!! Form::open(['url' => 'admin/gardens/objects/delete/' . $object->id]) !!}
<p>Delete <strong>{{ $object->object?->name ?? 'Garden Object #' . $object->id }}</strong>? Objects currently placed in a garden cannot be deleted.</p>
<div class="text-right">{!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}</div>
{!! Form::close() !!}
