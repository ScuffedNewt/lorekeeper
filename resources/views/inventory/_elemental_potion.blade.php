@if ($stack->first()->user_id == Auth::user()->id)
    <li class="list-group-item">
        <a class="card-title h5 collapse-title" data-toggle="collapse" href="#openBoxForm"> Consume Potion</a>
        <div id="openBoxForm" class="collapse">
            {!! Form::hidden('tag', $tag->tag) !!}
            <p><b>Select a Character to apply this potion to:</b></p>
            <div class="form-group">
                {!! Form::select('character_id', Auth::user()->characters()->get()->pluck('fullName', 'id'), null, ['class' => 'form-control']) !!}
            </div>
            <p>This action is not reversible. Are you sure you want to use this potion?</p>
            <div class="text-right">
                {!! Form::button('Open', ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'act', 'type' => 'submit']) !!}
            </div>
        </div>
    </li>
@else
    <div class="alert alert-info">
        Elemental Potions can only be applied by the owner of the item.
    </div>
@endif
