<div class="card mb-3">
    <div class="card-header h3">Options</div>
    <div class="card-body">
        <p>Fertiliser is required for some plants to grow, but can also reduce grow-time for plants below its quality requirement.</p>
        <p>
            E.g. if a plant requires 4 quality fertiliser to grow, but you use 5+ quality fertiliser, the plant be reduced by the 'time reduced' option and applied using the selected formula.
            <br>If a plant requires 4 quality fertiliser to grow, and you use 4 quality fertiliser, the plant will grow at its normal rate.
        </p>
        <div class="row">
            <div class="col-md form-group">
                {!! Form::label('Quality') !!}
                {!! Form::number('quality', isset($object) ? $object->data['quality'] : 1, ['class' => 'form-control mb-1', 'min' => 1]) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('Time Reduced (Optional) (Hours)') !!}
                {!! Form::number('time_reduced', isset($object) ? $object->data['time_reduced'] : 0, ['class' => 'form-control mb-1', 'min' => 0]) !!}
            </div>
        </div>

        <hr />

        <h5>Formula</h5>
        <p>
            <b>Flat Reduction (RECOMMENDED):</b> Reduces the grow-time by the amount specified in 'Time Reduced', irregardless of how much higher quality the fertiliser is.
            <br><b>Percentage Based Reduction:</b> Reduces the grow-time by used (quality / required quality) * time_reduced.
            <br>Example with numbers above: (5 / 4) * 2 = 2.5 hours reduced.
        </p>
        <div class="form-group">
            {!! Form::label('Formula') !!}
            {!! Form::select('formula', [0 => 'Flat Reduction', 1 => 'Percentage Based Reduction'], isset($object) ? $object->data['formula'] : 0, ['class' => 'form-control mb-1']) !!}
        </div>
    </div>
</div>

<script>
    $('[data-toggle="tooltip"]').tooltip({
        html: true
    });
</script>
