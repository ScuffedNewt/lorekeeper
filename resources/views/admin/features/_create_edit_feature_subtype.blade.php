{!! Form::label('Subtypes (Optional)') !!} {!! add_help('This is cosmetic and does not limit choice of traits in selections.') !!}
{!! Form::select('subtype_ids[]', $subtypes, $subtype_ids, ['class' => 'form-control selectize', 'id' => 'subtype', 'multiple', 'placeholder' => 'Select Subtypes']) !!}
