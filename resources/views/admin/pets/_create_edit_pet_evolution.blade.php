{!! Form::open(['url' => 'admin/data/pets/edit/' . $pet->id . '/evolution/' . ($evolution->id ? 'edit/' . $evolution->id : 'create'), 'files' => true]) !!}

<div class="form-group">
    {!! Form::text('evolution_name', $evolution->id ? $evolution->evolution_name : null, ['class' => 'form-control mr-2 feature-select', 'placeholder' => 'Evolution Name (Required)']) !!}
</div>

<p>The base pet is considered stage 0. All stages after that are considered evolutions.
    <br>When evolving the pet will automatically evolve into the next highest stage.
</p>
<div class="form-group">
    {!! Form::label('Evolution Stage (Required)') !!}
    {!! Form::number('evolution_stage', $evolution->id ? $evolution->evolution_stage : null, ['class' => 'form-control', 'placeholder' => 'Stage (Number)', 'min' => 1]) !!}
</div>

<div class="form-group">
    {!! Form::label('Image (Required)') !!}
    <div>{!! Form::file('evolution_image') !!}</div>
    <div class="text-muted">Recommended size: 200px x 200px</div>
    @if ($evolution->has_image)
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>

<div class="text-right">
    {!! Form::submit($evolution->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}
