<h3>Pairing Item</h3>

<p>This is where you can specifiy how this item influences the generated offspring.</p>

<hr>
<h3>Basics</h3>
<div class="row">
    <div class="col">
        {!! Form::label('Pairing Type (Optional)') !!} {!! add_help('Pairings can be restricted to either be between different species or between
        subtypes of the same species. Leave Empty if you want to allow all pairings.') !!}
    </div>
    <div class="col">
        @if(isset($tag->getData()['pairing_type']))
        {{ Form::select('pairing_type', ['Species', 'Subtype'], $tag->getData()['pairing_type'], ['class' => 'form-control mr-2', 'placeholder' => 'Select Pairing Type']) }}
        @else
        {{ Form::select('pairing_type', ['Species', 'Subtype'], null, ['class' => 'form-control mr-2', 'placeholder' => 'Select Pairing Type']) }}
        @endif
    </div>
</div>
<div class="row mt-3">
    <div class="col">
        {!! Form::label('Min Offspring generated') !!} {!! add_help('The minimum amount of slots/offspring to be generated.') !!}
        {!! Form::number('min', isset($tag->getData()['min'])? $tag->getData()['min'] : 1,  ['class' => 'form-control']) !!}  
    </div>
    <div class="col">
        {!! Form::label('Max Offspring generated') !!} {!! add_help('The maximum amount of slots/offspring to be generated.') !!}
        {!! Form::number('max', isset($tag->getData()['max']) ? $tag->getData()['max'] : 1, ['class' => 'form-control']) !!} 
    </div>
</div>

<hr>
<p> If a trait is set, this trait will be granted to all offspring and will list the second parent's species/subtype. It will not be set if the parents species + subtype are identical.
    If a species is set, the offspring will always be that species, but the MYO may have traits of either parent ignoring species restrictions.
    If neither is set, traits and species are chosen solely from the parent characters.
</p>
<div class="row">
    <div class="col">
        {!! Form::label('Offspring Trait (Optional)') !!} {!! add_help('Choose a trait that this pairing item will always grant the offspring.') !!}
    </div>
    <div class="col">
        @if(isset($tag->getData()['feature_id']))
        {!! Form::select('feature_id', $features, $tag->getData()['feature_id'], ['class' => 'form-control mr-2
        feature-select', 'placeholder' => 'Select Offspring Trait']) !!}
        @else
        {!! Form::select('feature_id', $features, null, ['class' => 'form-control mr-2 feature-select', 'placeholder' =>
        'Select Offspring Trait']) !!}
        @endif
    </div>
</div>

<div class="row">
    <div class="col">
        {!! Form::label('Offspring Species (Optional)') !!} {!! add_help('Choose a species that this pairing item will grant the offspring.') !!}
    </div>
    <div class="col">
        @if(isset($tag->getData()['species_id']))
        {!! Form::select('species_id', $specieses, $tag->getData()['species_id'], ['class' => 'form-control mr-2
        feature-select', 'placeholder' => 'Select Offspring Species']) !!}
        @else
        {!! Form::select('species_id', $specieses, null, ['class' => 'form-control mr-2 feature-select', 'placeholder'
        => 'Select Offspring Species']) !!}
        @endif
    </div>
</div>

<hr>
<h3>Restrictions (Optional)</h3>
<h5>Species Restrictions</h5>
<p>Add the species that this item should work on. For a pairing to go through using this item, at least one parent must
    have a species listed here. Leave it empty to allow all species to work.</p>

<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addSpecies">Add valid Species</a>
</div>


<table class="table table-sm" id="speciesTable">

    <tbody id="speciesTableBody">
        <tr class="loot-row hide">
            <td class="loot-row-select">
                {!! Form::select('legal_species_id[]', $specieses, null, ['class' => 'form-control item-select', 'placeholder'
                => 'Select Species']) !!}
            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-species-button">Remove</a></td>
        </tr>
        @if(isset($tag->getData()['legal_species_id']) && count($tag->getData()['legal_species_id']) > 0)
        @foreach($tag->getData()['legal_species_id'] as $legal_species_id)
        <tr class="loot-row">
            <td class="loot-row-select">
                {!! Form::select('legal_species_id[]', $specieses, $legal_species_id, ['class' => 'form-control item-select',
                'placeholder' => 'Select Species']) !!}

            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-species-button">Remove</a></td>
        </tr>
        @endforeach
        @endif

    </tbody>
</table>

<h5>Trait Restrictions</h5>
<p>Add the traits that this item may grant. Any trait not mentioned here will not be inheritable via this item. If you want all traits to be inheritable, leave this empty.</p>

<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addTrait">Add valid traits</a>
</div>


<table class="table table-sm" id="traitTable">

    <tbody id="traitTableBody">
        <tr class="loot-row hide">
            <td class="loot-row-select">
                {!! Form::select('legal_feature_id[]', $features, null, ['class' => 'form-control item-select', 'placeholder'
                => 'Select Trait']) !!}
            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-trait-button">Remove</a></td>
        </tr>
        @if(isset($tag->getData()['legal_feature_id']) && count($tag->getData()['legal_feature_id']) > 0)
        @foreach($tag->getData()['legal_feature_id'] as $legal_feature_id)
        <tr class="loot-row">
            <td class="loot-row-select">
                {!! Form::select('legal_feature_id[]', $features, $legal_feature_id, ['class' => 'form-control item-select',
                'placeholder' => 'Select Trait']) !!}

            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-trait-button">Remove</a></td>
        </tr>
        @endforeach
        @endif

    </tbody>
</table>


@section('scripts')
@parent
<script>
$(document).ready(function() {
    var $speciesTable = $('#speciesTableBody');
    var $speciesRow = $('#speciesTableBody').find('.hide');
    var $traitTable = $('#traitTableBody');
    var $traitRow = $('#traitTableBody').find('.hide');

    $('#speciesTableBody .selectize').selectize();
    attachRemoveListener($('#speciesTableBody .remove-species-button'));

    $('#traitTableBody .selectize').selectize();
    attachRemoveListener($('#traitTableBody .remove-trait-button'));

    $('#addSpecies').on('click', function(e) {
        e.preventDefault();
        var $clone = $speciesRow.clone();
        $clone.removeClass('hide');

        $speciesTable.append($clone);
        attachRemoveListener($clone.find('.remove-species-button'));
    });

    $('#addTrait').on('click', function(e) {
        e.preventDefault();
        var $clone = $traitRow.clone();
        $clone.removeClass('hide');

        $traitTable.append($clone);
        attachRemoveListener($clone.find('.remove-trait-button'));
    });


    function attachRemoveListener(node) {
        node.on('click', function(e) {
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    }
});
</script>
@endsection