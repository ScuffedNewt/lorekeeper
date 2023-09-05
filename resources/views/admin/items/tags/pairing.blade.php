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
        {{ Form::select('pairing_type', ['Species', 'Subtype'], $tag->getData()['pairing_type'] ?? null, ['class' => 'form-control mr-2', 'placeholder' => 'Select Pairing Type']) }}
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
    If a species is set, the offspring will always be that species, but the MYO may have traits of either parent ignoring species restrictions. If a subtype is set, it will always be passed on if the species matches.
    If neither is set, traits and species are chosen solely from the parent characters.
</p>
<div class="row">
    <div class="col">
        {!! Form::label('Offspring Trait (Optional)') !!} {!! add_help('Choose a trait that this pairing item will always grant the offspring.') !!}
    </div>
    <div class="col">
        {!! Form::select('feature_id', $features, $tag->getData()['feature_id'] ?? null, ['class' => 'form-control mr-2
        feature-select', 'placeholder' => 'Select Offspring Trait']) !!}
    </div>
</div>

<div class="row">
    <div class="col">
        {!! Form::label('Offspring Species (Optional)') !!} {!! add_help('Choose a species that this pairing item will grant the offspring.') !!}
    </div>
    <div class="col">
        {!! Form::select('species_id', $specieses, $tag->getData()['species_id'] ?? null, ['class' => 'form-control mr-2
        feature-select', 'placeholder' => 'Select Offspring Species']) !!}
    </div>
</div>

<div class="row">
    <div class="col">
        {!! Form::label('Offspring Subtype (Optional)') !!} {!! add_help('Choose a subtype that this pairing item will always grant the offspring. Will not work if the species does not match the subtype.') !!}
    </div>
    <div class="col">
        {!! Form::select('subtype_id', $subtypes, $tag->getData()['subtype_id'] ?? null, ['class' => 'form-control mr-2
        feature-select', 'placeholder' => 'Select Offspring Subtype']) !!}
    </div>
</div>

<hr>
<h3>Restrictions (Optional)</h3>
<h5>Species Exclusions</h5>
<p>Species set here cannot be inherited through a pairing using this item. If both parents are of an excluded species, the pairing cannot be created unless a default species is set.
    If one parent's species is not excluded, it always rolls that parent's species.
</p>

<div class="row">
    <div class="col">
        {!! Form::label('Default Species (Optional)') !!} {!! add_help('Choose a species that should be set if both parent species are excluded.') !!}
    </div>
    <div class="col">
        {!! Form::select('default_species_id', $specieses, $tag->getData()['default_species_id'] ?? null, ['class' => 'form-control mr-2
        feature-select', 'placeholder' => 'Select Default Species']) !!}
    </div>
</div>

<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addSpecies">Add Species</a>
</div>

<table class="table table-sm" id="speciesTable">
    <tbody id="speciesTableBody">
        <tr class="loot-row hide">
            <td class="loot-row-select">
                {!! Form::select('illegal_species_id[]', $specieses, null, ['class' => 'form-control item-select', 'placeholder'
                => 'Select Species']) !!}
            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-species-button">Remove</a></td>
        </tr>
        @if(isset($tag->getData()['illegal_species_id']) && count($tag->getData()['illegal_species_id']) > 0)
        @foreach($tag->getData()['illegal_species_id'] as $illegal_species_id)
        <tr class="loot-row">
            <td class="loot-row-select">
                {!! Form::select('illegal_species_id[]', $specieses, $illegal_species_id, ['class' => 'form-control item-select',
                'placeholder' => 'Select Species']) !!}

            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-species-button">Remove</a></td>
        </tr>
        @endforeach
        @endif

    </tbody>
</table>

<h5>Subtype Exclusions</h5>
<p>Subtype set here cannot be inherited through a pairing using this item. If both parents have an excluded subtype, the pairing cannot be created unless a default subtype is set.
If one parent's subtype is not excluded, it always rolls that parent's subtype.
</p>

<div class="row">
    <div class="col">
        {!! Form::label('Default Subtype (Optional)') !!} {!! add_help('Choose a subtype that should be set if both parent subtypes are excluded.') !!}
    </div>
    <div class="col">
        {!! Form::select('default_subtype_id', $subtypes, $tag->getData()['default_subtype_id'] ?? null, ['class' => 'form-control mr-2
        feature-select', 'placeholder' => 'Select Default Subtype']) !!}
    </div>
</div>

<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addSubtype">Add Subtype</a>
</div>

<table class="table table-sm" id="subtypeTable">

    <tbody id="subtypeTableBody">
        <tr class="loot-row hide">
            <td class="loot-row-select">
                {!! Form::select('illegal_subtype_id[]', $subtypes, null, ['class' => 'form-control item-select', 'placeholder'
                => 'Select Subtype']) !!}
            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-subtype-button">Remove</a></td>
        </tr>
        @if(isset($tag->getData()['illegal_subtype_id']) && count($tag->getData()['illegal_subtype_id']) > 0)
        @foreach($tag->getData()['illegal_subtype_id'] as $illegal_subtype_id)
        <tr class="loot-row">
            <td class="loot-row-select">
                {!! Form::select('illegal_subtype_id[]', $subtypes, $illegal_subtype_id, ['class' => 'form-control item-select',
                'placeholder' => 'Select Subtype']) !!}

            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-subtype-button">Remove</a></td>
        </tr>
        @endforeach
        @endif

    </tbody>
</table>

<h5>Trait Exclusions</h5>
<p>Traits set here cannot be inherited through a pairing using this item.</p>

<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addTrait">Add traits</a>
</div>


<table class="table table-sm" id="traitTable">

    <tbody id="traitTableBody">
        <tr class="loot-row hide">
            <td class="loot-row-select">
                {!! Form::select('illegal_feature_id[]', $features, null, ['class' => 'form-control item-select', 'placeholder'
                => 'Select Trait']) !!}
            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-trait-button">Remove</a></td>
        </tr>
        @if(isset($tag->getData()['illegal_feature_id']) && count($tag->getData()['illegal_feature_id']) > 0)
        @foreach($tag->getData()['illegal_feature_id'] as $illegal_feature_id)
        <tr class="loot-row">
            <td class="loot-row-select">
                {!! Form::select('illegal_feature_id[]', $features, $illegal_feature_id, ['class' => 'form-control item-select',
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
    var $subtypeTable = $('#subtypeTableBody');
    var $subtypeRow = $('#subtypeTableBody').find('.hide');

    $('#speciesTableBody .selectize').selectize();
    attachRemoveListener($('#speciesTableBody .remove-species-button'));

    $('#traitTableBody .selectize').selectize();
    attachRemoveListener($('#traitTableBody .remove-trait-button'));

    $('#subtypeTableBody .selectize').selectize();
    attachRemoveListener($('#subtypeTableBody .remove-subtype-button'));
    
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

    $('#addSubtype').on('click', function(e) {
        e.preventDefault();
        var $clone = $subtypeRow.clone();
        $clone.removeClass('hide');

        $subtypeTable.append($clone);
        attachRemoveListener($clone.find('.remove-subtype-button'));
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