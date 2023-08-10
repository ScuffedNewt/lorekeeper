<h3>Boost Item</h3>

<p>This is where you can specifiy which percentages during the pairing process this item boosts. The percentage is only applied if you chose a setting or rarity!</p>

<hr>
<h3>Settings</h3>
<div class="row">
    <div class="col">
        {!! Form::label('Setting Type') !!} {!! add_help('This overrides the default percentages from the site settings.') !!}
    </div>
    <div class="col">
        @if(isset($tag->getData()['setting']))
        {{ Form::select('setting', $settings, $tag->getData()['setting'], ['class' => 'form-control mr-2', 'placeholder' => 'Select Setting']) }}
        @else
        {{ Form::select('setting', $settings, null, ['class' => 'form-control mr-2', 'placeholder' => 'Select Setting']) }}
        @endif
    </div>
</div>
<div class="row mt-3">
    <div class="col">
        {!! Form::label('New Percentage') !!} {!! add_help('The new chance upon using this boost item. A value from 1-100.') !!}
        {!! Form::number('setting_chance', isset($tag->getData()['setting_chance'])? $tag->getData()['setting_chance'] : 50,  ['class' => 'form-control']) !!}  
    </div>
</div>


<hr>
<h3>Rarities</h3>
<div class="row">
    <div class="col">
        {!! Form::label('Rarity') !!} {!! add_help('This overrides the default percentages from the site settings.') !!}
    </div>
    <div class="col">
        @if(isset($tag->getData()['rarity_id']))
        {{ Form::select('rarity_id', $rarities, $tag->getData()['rarity_id'], ['class' => 'form-control mr-2', 'placeholder' => 'Select Rarity']) }}
        @else
        {{ Form::select('rarity_id', $rarities, null, ['class' => 'form-control mr-2', 'placeholder' => 'Select Rarity']) }}
        @endif
    </div>
</div>
<div class="row mt-3">
    <div class="col">
        {!! Form::label('New Percentage') !!} {!! add_help('The new chance upon using this boost item. A value from 1-100.') !!}
        {!! Form::number('rarity_chance', isset($tag->getData()['rarity_chance'])? $tag->getData()['rarity_chance'] : 50,  ['class' => 'form-control']) !!}  
    </div>
</div>


@section('scripts')
@parent
@endsection