<p>Create a new pairing of characters. If you pair your character with one that belongs to another person, it is highly recommended you ask them first, as their approval will be needed.</p>

{!! Form::open(['url' => 'characters/pairings/create', 'id' => 'pairingForm']) !!}
    <div id="characterComponents" class="row justify-content-center">
        <div class="submission-character m-3 card col-md" id="character_1">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 align-items-stretch d-flex">
                        <div class="d-flex text-center align-items-center">
                            <div class="character-image-blank">Select character.</div>
                            <div class="character-image-loaded hide"></div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <a href="#" class="float-right fas fa-close"></a>
                        <div class="form-group">
                            {!! Form::label('character_codes', 'First Character') !!}
                            {!! Form::select('character_codes[]', $characters, null, ['class' => 'form-control selectize character-code', 'placeholder' => 'Select Character']) !!}
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
                            {!! Form::label('character_codes', 'Second Character') !!}
                            {!! Form::select('character_codes[]', $characters, null, ['class' => 'form-control selectize character-code', 'placeholder' => 'Select Character']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h2>Addon Items</h2>
    <p>
        Decide which pairing item and boosts to use. These items will be removed from your inventory but refunded if your pairing is rejected.
        You can optionally attach Boost Items.
    </p>
    <div class="row">
        <div class="col-md-6">
            <h3>Pairing Item</h3>
            <p>
                Decide which pairing item to use.
            </p>
            @if($user_pairing_items)
                @include('widgets._inventory_select', [
                    'user' => Auth::user(), 'inventory' => $user_pairing_items, 'categories' => $categories,
                    'selected' => [], 'page' => $page, 'id' => 'pairing_item_id', 'item_filter' => $pairing_item_filter
                ])
            @else
                <div class="alert alert-danger">
                    You have no pairing items. You cannot create a pairing.
                </div>
            @endif
        </div>
        <div class="col-md-6">
            <h3>Boost Items</h3>
            <p>
                Decide which boost items to use. Boost items are optional.
            </p>
            @if(count($user_boost_items) > 0)
                @include('widgets._inventory_select', [
                    'user' => Auth::user(), 'inventory' => $user_boost_items, 'categories' => $categories,
                    'selected' => [], 'page' => $page, 'id' => 'boost_item_id', 'item_filter' => $boost_item_filter
                ])
            @else
                <div class="alert alert-info">
                    You have no boost items. You can still create a pairing.
                </div>
            @endif
        </div>
    </div>

<div class="text-right">
    {!! Form::submit('Create Pairing', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}
