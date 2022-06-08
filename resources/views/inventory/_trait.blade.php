<li class="list-group-item">
    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#traitAddForm"> Add Trait To Character</a>
    <div id="traitAddForm" class="collapse">
        {!! Form::hidden('tag', $tag->tag) !!}

        <div class="form-group">
            {!! Form::label('character_id', 'Select Character') !!}
            {!! Form::select('character_id', Auth::user()->characters()->get()->pluck('fullname', 'id'), null, ['class'=>'form-control', 'placeholder' => 'Select Character']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('feature_id', 'Select Trait') !!}
            {!! Form::select('feature_id', $tag->getData()['features'], null, ['class'=>'form-control', 'placeholder' => 'Select Trait']) !!}
        </div>

        <p>This action is not reversible. Are you sure you want to apply the selected trait?</p>
        <div class="text-right">
            {!! Form::button('Apply Trait', ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'act', 'type' => 'submit']) !!}
        </div>
    </div>
</li>