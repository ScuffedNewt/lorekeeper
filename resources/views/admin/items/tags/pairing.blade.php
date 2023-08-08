<h3>Pairing</h3>

<p>This is where you can link a trait to a pairing item, as well as restrict which species this item can be used for.</p>


<div class="row">
    <div class="col">
        {!! Form::label('Pairing Type') !!} {!! add_help('Decides whether or not this item allows crossbreeds of different species or just amongst subtypes of one species.') !!}
    </div>
    <div class="col">
        @if(isset($tag->getData()['pairing_type']))
            {{ Form::select('pairing_type', ['Species', 'Subtype'], $tag->getData()['pairing_type'], ['class' => 'form-control mr-2', 'placeholder' => 'Select Pairing Type']) }}
        @else
            {{ Form::select('pairing_type', ['Species', 'Subtype'], null, ['class' => 'form-control mr-2', 'placeholder' => 'Select Pairing Type']) }}
        @endif
    </div>
</div>


<div class="row">
    <div class="col">
        {!! Form::label('Trait') !!} {!! add_help('Choose the trait that this pairing item will grant. Eg. Hybrid / Crossbreed.') !!}
    </div>
    <div class="col">
        @if(isset($tag->getData()['feature_id']))
            {!! Form::select('feature_id', $features, $tag->getData()['feature_id'], ['class' => 'form-control mr-2 feature-select', 'placeholder' => 'Select Trait']) !!}
        @else
            {!! Form::select('feature_id', $features, null, ['class' => 'form-control mr-2 feature-select', 'placeholder' => 'Select Trait']) !!}
        @endif
    </div>
</div>

<hr>

{!! Form::label('Species') !!} {!! add_help('Add the species that this item should work on.') !!}

<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addLoot">Add Species</a>
</div>

<table class="table table-sm" id="lootTable">
   
    <tbody id="lootTableBody">
        @if(isset($tag->getData()['species_ids']) && count($tag->getData()['species_ids']) > 0)
            @foreach($tag->getData()['species_ids'] as $species_id)
                <tr class="loot-row">
                    <td class="loot-row-select">
                        {!! Form::select('species_id[]', $specieses, $species_id, ['class' => 'form-control item-select', 'placeholder' => 'Select Species']) !!}
                      
                    </td>
                    <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
                </tr>
            @endforeach
        @else
            <tr class="loot-row">
                    <td class="loot-row-select">
                        {!! Form::select('species_id[]', $specieses, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Species']) !!}
                    </td>
                    <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
            </tr>
        @endif
    </tbody>
</table>

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    var $lootTable  = $('#lootTableBody');
    var $lootRow = $('#lootTableBody').find('.loot-row');

    $('#lootTableBody .selectize').selectize();
    attachRemoveListener($('#lootTableBody .remove-loot-button'));


    $('#addLoot').on('click', function(e) {
        e.preventDefault();
        var $clone = $lootRow.clone();
        $lootTable.append($clone);
        attachRemoveListener($clone.find('.remove-loot-button'));
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