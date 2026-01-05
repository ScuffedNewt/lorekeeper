<h4>Populate Default Criteria</h4>
<p>You can populate this {{ $type }} with the selected defaults.</p>
@php
    $defaults = \App\Models\Criteria\CriterionDefault::orderBy('name')->get();
@endphp
<div class="row">
    @if (count($defaults))
        @foreach ($defaults as $default)
            <div class="col-md form-group">
                {!! Form::checkbox('default_criteria[' . $default->id . ']', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('default_criteria[' . $default->id . ']', $default->name, ['class' => 'form-check-label ml-3']) !!} {!! add_help('Toggle on to populate this criterion set.') !!}
            </div>
        @endforeach
    @else
        <div class="col-md">
            <div class="alert alert-info w-100">
                No default criteria have been created yet. You can create them under the "Criteria Rewards" section of the admin panel.
            </div>
        </div>
    @endif
</div>
