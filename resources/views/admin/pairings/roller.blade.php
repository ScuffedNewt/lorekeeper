@extends('admin.layout')

@section('admin-title') Pairing Roller @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Pairing Roller' => 'admin/pairings/roller']) !!}

<h1>
    Pairing Roller
</h1>

<p>Here, you can test the pairing logic by rolling MYO slots for two different characters. All pairing and boost items
    are available to test. This roller does not actually create a MYO slot,
    rather it shows the results of it.
</p>
<div id="characters" class="mb-3">
    @if(isset($pairing_character_1))
    @include('widgets._character_select_entry', ['character' => $pairing_character_1])
    @endif
    @if(isset($pairing_character_2))
    @include('widgets._character_select_entry', ['character' => $pairing_character_2])
    @endif

</div>

{!! Form::open(['url' => url()->current(), 'id' => 'pairingForm']) !!}
<div id="characterComponents" class="row justify-content-center">
    <div class="submission-character m-3 card col-md" id="character_1">
        <div class="card-body">

            <div class="row">
                <div class="col-md-4 align-items-stretch d-flex">
                    <div class="d-flex text-center align-items-center">
                        <div class="character-image-blank">Enter character code.</div>
                        <div class="character-image-loaded hide"></div>
                    </div>
                </div>
                <div class="col-md-8">
                    <a href="#" class="float-right fas fa-close"></a>
                    <div class="form-group">
                        {!! Form::label('character_1_code', 'Character Code 1') !!}
                        {!! Form::text('character_1_code', $slug1 ?? null, ['class' => 'form-control character-code']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="submission-character m-3 card col-md" id="character_2">
        <div class="card-body">

            <div class="row">
                <div class="col-md-4 align-items-stretch d-flex">
                    <div class="d-flex text-center align-items-center">
                        <div class="character-image-blank">Enter character code.</div>
                        <div class="character-image-loaded hide"></div>
                    </div>
                </div>
                <div class="col-md-8">
                    <a href="#" class="float-right fas fa-close"></a>
                    <div class="form-group">
                        {!! Form::label('character_2_code', 'Character Code 2') !!}
                        {!! Form::text('character_2_code',  $slug2 ?? null, ['class' => 'form-control character-code']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<h2>Pairing Items </h2>
<p>
    Decide which pairing item and boosts to use. For a successful pairing, you need to attach at least one valid Pairing Item. You can optionally attach Boost Items.
</p>
<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addItem">Add Items</a>
</div>


<table class="table table-sm" id="traitTable">
    <tbody id="itemTableBody">
        <tr class="loot-row hide">
            <td class="loot-row-select">
                {!! Form::select('item_id[]', $inventory, null, ['class' => 'form-control item-select', 'placeholder'
                => 'Select Item']) !!}
            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-trait-button">Remove</a></td>
        </tr>
        @if(isset($item_ids) && count($item_ids) > 0)
        @foreach($item_ids as $id)
        <tr class="loot-row">
            <td class="loot-row-select">
                {!! Form::select('item_id[]', $inventory, $id, ['class' => 'form-control item-select',
                'placeholder' => 'Select Item']) !!}
            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-trait-button">Remove</a></td>
        </tr>
        @endforeach
        @endif
    </tbody>
</table>

<div class="text-right">
    <a href="#" class="btn btn-secondary" id="pairingSubmit">Roll!</a>
</div>
{!! Form::close() !!}


<h3>Results</h3>

<p>The results of your test roll will appear here!</p>

@if(isset($testMyos))
<h5>Pairing of: <a href="/character/{{ $slug1 }}">{{ $slug1 }}</a> & <a href="/character/{{ $slug2 }}">{{ $slug2 }}</a></h5>
<div class="row">
    @foreach($testMyos as $test)
    <div class="col col-lg-3 card character-bio w-100 p-3 m-4">
        <div class="row">
            <div class="col-lg-4 col-md-6 col-4">
                <h5>Species</h5>
            </div>
            <div class="col-lg-8 col-md-6 col-8">{!! $test['species'] !!}</div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 col-4">
                <h5>Subtype</h5>
            </div>
            <div class="col-lg-8 col-md-6 col-8">{!! $test['subtype'] !!}</div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 col-4">
                <h5>Rarity</h5>
            </div>
            <div class="col-lg-8 col-md-6 col-8">{!! $test['rarity'] !!}</div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 col-4">
                <h5>Sex</h5>
            </div>
            <div class="col-lg-8 col-md-6 col-8">{!! $test['sex'] !!}</div>
        </div>
        <div class="mb-3">
            <div>
                <h5>Traits</h5>
            </div>
            
            <div>
                @if(count($test['features']) > 0)
                @foreach($test['features'] as $feature)
                <div> <strong>{!! $feature->name !!}:</strong> ({{ $feature->rarity->name }}) ({{ $test['feature_data'][$loop->index] }})</div>
                @endforeach
                @else
                <div>No traits listed.</div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection

@section('scripts')
@parent

<script>
$(document).ready(function() {
    var $char_1 = $('#character_1');
    var $char_2 = $('#character_2');

    attachListeners($char_1)
    attachListeners($char_2)

    var $pairingForm = $('#pairingForm');
    var $pairingSubmit = $('#pairingSubmit');


    $pairingSubmit.on('click', function(e) {
        e.preventDefault();
        $pairingForm.submit();
    });


    function attachListeners(node) {
        node.find('.character-code').on('change', function(e) {
            var $parent = $(this).parent().parent().parent().parent();
            $parent.find('.character-image-loaded').load('{{ url('submissions/new/character') }}/' + $(this).val(),
                function(response, status, xhr) {
                    $parent.find('.character-image-blank').addClass('hide');
                    $parent.find('.character-image-loaded').removeClass('hide');
                    $parent.find('.character-rewards').removeClass('hide');
                });
        });
    }


    var $traitTable = $('#itemTableBody');
    var $traitRow = $('#itemTableBody').find('.hide');

    $('#itemTableBody .selectize').selectize();
    attachRemoveListener($('#itemTableBody .remove-trait-button'));

    $('#addItem').on('click', function(e) {
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
$('.selectize').selectize();
</script>

@endsection