@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s Pets
@endsection

@section('meta-img')
    {{ $character->image->thumbnailUrl }}
@endsection

@section('profile-content')
    @if ($character->is_myo_slot)
        {!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url, 'Pets' => $character->url . '/pets']) !!}
    @else
        {!! breadcrumbs([
            $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
            $character->fullName => $character->url,
            'Pets' => $character->url . '/pets',
        ]) !!}
    @endif

    @include('character._header', ['character' => $character])

    <h1>Pets</h1>

    @if (Auth::check() && (Auth::user()->id == $character->user_id || Auth::user()->hasPower('manage_characters')))
        <p>
            Currently {{ config('lorekeeper.pets.display_pet_count') }} pet{{ config('lorekeeper.pets.display_pet_count') != 1 ? 's' : '' }} are displayed on the character's page.
            @if (config('lorekeeper.pets.max_pets') && config('lorekeeper.pets.max_pets') > 0)
                A maximum of {{ config('lorekeeper.pets.max_pets') }} pet{{ config('lorekeeper.pets.max_pets') != 1 ? 's' : '' }} can be attached.
            @endif
            <br />You can determine which pets are displayed by dragging and dropping them in the order you want.
        </p>

        {!! Form::open(['url' => 'characters/' . $character->slug . '/pets/sort', 'class' => 'text-right']) !!}
        {!! Form::hidden('sort', null, ['id' => 'sortableOrder']) !!}
        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    @endif

    <div id="sortable" class="row sortable justify-content-center">
        @foreach ($character->pets()->orderBy('sort', 'DESC')->get() as $pet)
            <div class="col-md-3 col-6 mb-3" data-id="{{ $pet->id }}">
                <div class="card inventory-category h-100" data-id="{{ $pet->id }}">
                    <div class="card-body inventory-body text-center">
                        <div class="mb-1">
                            <a href="{{ $pet->pageUrl() }}" class="inventory-stack">
                                <img src="{{ $pet->pet->image($pet->id) }}" class="rounded img-fluid" />
                            </a>
                        </div>
                        <div>
                            @if ($pet->pet_name)
                                <a href="{{ $pet->pageUrl() }}">
                                    <div class="text-light btn btn-dark">{!! $pet->pet_name !!}</div>
                                </a>
                            @endif
                            <div>{!! $pet->pet->displayName !!}</div>
                        </div>
                        @if (config('lorekeeper.pets.pet_bonding_enabled'))
                            <div class="progress mb-2">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: {{ ($pet->level?->nextLevel?->bonding_required ? ($pet->level?->bonding / $pet->level?->nextLevel?->bonding_required) * 100 : 1 * 100) . '%' }}" aria-valuenow="{{ $pet->level?->bonding }}"
                                    aria-valuemin="0" aria-valuemax="{{ $pet->level?->nextLevel?->bonding_required ?? 100 }}">
                                    {{ $pet->level?->nextLevel?->bonding_required ? $pet->level?->bonding . '/' . $pet->level?->nextLevel?->bonding_required : $pet->level?->levelName }}
                                </div>
                            </div>
                            {{ $pet->level?->levelName }}
                            @if (Auth::check() && Auth::user()->id == $character->user_id && $pet->canBond())
                                <div class="form-group mb-0" id="bondForm">
                                    {!! Form::open(['url' => 'pets/bond/' . $pet->id]) !!}
                                    {!! Form::submit('Bond', ['class' => 'btn btn-primary', 'id' => 'bond']) !!}
                                    {!! Form::close() !!}
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">{{ $pet->canBond(true) }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            // when form is submitted disable button and hide form
            $('#bondForm').submit(function(e) {
                e.preventDefault();
                $('#bond').prop('disabled', true);
                $('#bondForm').hide();

                // submit form
                e.target.submit();
            });

            $("#sortable").sortable({
                characters: '.sort-item',
                placeholder: "sortable-placeholder col-md-3 col-6",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
