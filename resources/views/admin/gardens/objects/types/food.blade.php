<div class="card mb-3">
    <div class="card-header h3">Options</div>
    <div class="card-body">
        <div class="form-group">
            {!! Form::label('Amount of Hunger Satisfied') !!} {!! add_help('The amount of hunger this food will satisfy when consumed. More hunger satisfied means the food is more valuable.') !!}
            {!! Form::number('hunger', isset($object) ? $object->data['hunger'] : 1, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>

<script>
    $('[data-toggle="tooltip"]').tooltip({
        html: true
    });
</script>
