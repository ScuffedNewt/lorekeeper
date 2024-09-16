@php
    // find typing of this object
    $type = \App\Models\Element\Typing::where('typing_model', get_class($object))
        ->where('typing_id', $object->id)
        ->first();
    $type = $type ?? null;
@endphp

{!! $type ? $type->displayElements : '' !!}
