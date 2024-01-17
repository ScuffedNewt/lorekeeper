    <h2>Pairing Item</h2>
    <p>This is where you can specifiy how this item influences the generated offspring.</p>

    <hr>
    <h3>Basics</h3>
    <p>Pairings can be restricted to either be between the same species, or between the same subtypes of the same species.
        <br><b>Leave empty if you want to allow all pairings.</b>
    </p>
    <div class="form-group">
        {!! Form::select('pairing_type', ['Species', 'Subtype'], $tag->getData()['pairing_type'] ?? null, ['class' => 'form-control mr-2', 'placeholder' => 'Select Pairing Type']) !!}
    </div>
    <div class="row mt-3">
        <div class="col">
            {!! Form::label('Min Offspring generated') !!} {!! add_help('The minimum amount of slots/offspring to be generated.') !!}
            {!! Form::number('min', isset($tag->getData()['min']) ? $tag->getData()['min'] : 1, ['class' => 'form-control']) !!}
        </div>
        <div class="col">
            {!! Form::label('Max Offspring generated') !!} {!! add_help('The maximum amount of slots/offspring to be generated.') !!}
            {!! Form::number('max', isset($tag->getData()['max']) ? $tag->getData()['max'] : 1, ['class' => 'form-control']) !!}
        </div>
    </div>

    <hr>
    <h3>Offspring Traits (Optional)</h3>
    <p>
        If a trait is set, this trait will be granted to all offspring that are created using this item.
        <br><b>Other pairing items can still inherit this trait if present in one or both parents.</b>
        <br><br>If a species is set, the offspring will always be that species, but the MYO may have traits of either parent ignoring species restrictions.
        <br>If a subtype is set, it will always be passed on if the species matches.
        <br>If neither is set, traits and species are chosen solely from the parent characters.
        <br><br><b>These will override the default species etc. below</b>
    </p>
    <div class="form-group">
        {!! Form::label('Guarenteed Offspring Trait (Optional)') !!}
        <p>Choose a trait that this pairing item will always grant the offspring.</p>
        {!! Form::select('feature_id', $features, $tag->getData()['feature_id'] ?? null, [
            'class' => 'form-control mr-2
                                                        feature-select',
            'placeholder' => 'Select Offspring Trait',
        ]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Guarenteed Offspring Species (Optional)') !!}
        <p>Choose a species that this pairing item will grant the offspring.</p>
        {!! Form::select('species_id', $specieses, $tag->getData()['species_id'] ?? null, [
            'class' => 'form-control mr-2
                                                        feature-select',
            'placeholder' => 'Select Offspring Species',
        ]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Guarenteed Offspring Subtype (Optional)') !!}
        <p>Choose a subtype that this pairing item will always grant the offspring. Will not work if the species does not match the subtype.</p>
        {!! Form::select('subtype_id', $subtypes, $tag->getData()['subtype_id'] ?? null, [
            'class' => 'form-control mr-2
                                                        feature-select',
            'placeholder' => 'Select Offspring Subtype',
        ]) !!}
    </div>

    <hr>
    <h3>Restrictions (Optional)</h3>
    <h5>Species Exclusions</h5>
    <p>
        Species set here cannot be inherited through a pairing using this item. If both parents are of an excluded species, the pairing cannot be created unless a default species is set.
        <br><b>If one parent's species is not excluded, it always rolls that parent's species.</b>
    </p>

    <div class="form-group">
        {!! Form::label('Default Species (Optional)') !!} {!! add_help('Choose a species that should be set if both parent species are excluded.') !!}
        {!! Form::select('default_species_id', $specieses, $tag->getData()['default_species_id'] ?? null, [
            'class' => 'form-control mr-2
                                                        feature-select',
            'placeholder' => 'Select Default Species',
        ]) !!}
    </div>

    <table class="table table-sm" id="speciesTable">
        <tbody id="speciesTableBody">
            <tr class="loot-row hide">
                <td class="loot-row-select border-0">
                    {!! Form::select('illegal_species_ids[]', $specieses, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Species']) !!}
                </td>
                <td class="text-right border-0"><a href="#" class="btn btn-danger remove-species-button">Remove</a></td>
            </tr>
            @if (isset($tag->getData()['illegal_species_ids']) && count($tag->getData()['illegal_species_ids']) > 0)
                @foreach ($tag->getData()['illegal_species_ids'] as $illegal_species_id)
                    <tr class="loot-row">
                        <td class="loot-row-select border-0">
                            {!! Form::select('illegal_species_ids[]', $specieses, $illegal_species_id, ['class' => 'form-control item-select', 'placeholder' => 'Select Species']) !!}
                        </td>
                        <td class="text-right border-0"><a href="#" class="btn btn-danger remove-species-button">Remove</a></td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="text-right mb-3">
        <a href="#" class="btn btn-outline-info my-2" id="addSpecies">Add Excluded Species</a>
    </div>

    <h5>Subtype Exclusions</h5>
    <p>
        Subtype set here cannot be inherited through a pairing using this item. If both parents have an excluded subtype, the pairing will have no subtype unless a default subtype is set.
        <br>If one parent's subtype is not excluded, it always rolls that parent's subtype.
    </p>

    <div class="form-group">
        {!! Form::label('Default Subtype (Optional)') !!}
        <p>Choose a subtype that should be set if both parent subtypes are excluded.</p>
        {!! Form::select('default_subtype_ids', $subtypes, $tag->getData()['default_subtype_ids'] ?? null, [
            'class' => 'form-control mr-2
                                                        feature-select',
            'placeholder' => 'Select Default Subtype',
        ]) !!}
    </div>

    <table class="table table-sm" id="subtypeTable">
        <tbody id="subtypeTableBody">
            <tr class="loot-row hide">
                <td class="loot-row-select border-0">
                    {!! Form::select('illegal_subtype_ids[]', $subtypes, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Subtype']) !!}
                </td>
                <td class="text-right border-0"><a href="#" class="btn btn-danger remove-subtype-button">Remove</a></td>
            </tr>
            @if (isset($tag->getData()['illegal_subtype_ids']) && count($tag->getData()['illegal_subtype_ids']) > 0)
                @foreach ($tag->getData()['illegal_subtype_ids'] as $illegal_subtype_id)
                    <tr class="loot-row">
                        <td class="loot-row-select border-0">
                            {!! Form::select('illegal_subtype_ids[]', $subtypes, $illegal_subtype_id, ['class' => 'form-control item-select', 'placeholder' => 'Select Subtype']) !!}

                        </td>
                        <td class="text-right border-0"><a href="#" class="btn btn-danger remove-subtype-button">Remove</a></td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="text-right my-3">
        <a href="#" class="btn btn-outline-info" id="addSubtype">Add Excluded Subtype</a>
    </div>

    <h5>Trait Exclusions</h5>
    <p>
        Traits set here cannot be inherited through a pairing using this item, even if one or both of the parents possess the trait.
        <br><b>Other pairing items can still inherit this trait. If you want a trait to be uninheritable you should set its category to uninheritable or its rarity.</b>
    </p>

    <table class="table table-sm" id="traitTable">
        <tbody id="traitTableBody">
            <tr class="loot-row hide">
                <td class="loot-row-select border-0">
                    {!! Form::select('illegal_feature_ids[]', $features, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Trait']) !!}
                </td>
                <td class="text-right border-0"><a href="#" class="btn btn-danger remove-trait-button">Remove</a></td>
            </tr>
            @if (isset($tag->getData()['illegal_feature_ids']) && count($tag->getData()['illegal_feature_ids']) > 0)
                @foreach ($tag->getData()['illegal_feature_ids'] as $illegal_feature_id)
                    <tr class="loot-row">
                        <td class="loot-row-select border-0">
                            {!! Form::select('illegal_feature_ids[]', $features, $illegal_feature_id, ['class' => 'form-control item-select', 'placeholder' => 'Select Trait']) !!}

                        </td>
                        <td class="text-right border-0"><a href="#" class="btn btn-danger remove-trait-button">Remove</a></td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="text-right mb-3">
        <a href="#" class="btn btn-outline-info" id="addTrait">Add Excluded Trait</a>
    </div>

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
