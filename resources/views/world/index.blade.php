@extends('world.layout')

@section('world-title')
    Home
@endsection

@section('content')
    {!! breadcrumbs(['Encyclopedia' => 'world']) !!}

    <h1>World</h1>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ asset('images/characters.png') }}" alt="Characters" />
                    <h5 class="card-title">Characters</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="{{ url('world/species') }}">Species</a></li>
                    <li class="list-group-item"><a href="{{ url('world/subtypes') }}">Subtypes</a></li>
                    <li class="list-group-item"><a href="{{ url('world/rarities') }}">Rarities</a></li>
                    <li class="list-group-item"><a href="{{ url('world/trait-categories') }}">Trait Categories</a></li>
                    <li class="list-group-item"><a href="{{ url('world/traits') }}">All Traits</a></li>
                    @if (config('lorekeeper.extensions.visual_trait_index.enable_universal_index'))
                        <li class="list-group-item"><a href="{{ url('world/universaltraits') }}">Universal Trait Index</a></li>
                    @endif
                    @if (config('lorekeeper.extensions.visual_trait_index.enable_all_trait_index'))
                        <li class="list-group-item"><a href="{{ url('world/all-traits-index') }}">All Traits Index</a></li>
                    @endif
                    <li class="list-group-item"><a href="{{ url('world/character-categories') }}">Character Categories</a></li>
                    <li class="list-group-item"><a href="{{ url('world/elements') }}">All Elements</a></li>
                    @if (config('lorekeeper.claymores_and_companions.visibility_settings.character_classes'))
                        <li class="list-group-item"><a href="{{ url('world/character-classes') }}">Character Classes</a></li>
                    @endif
                    @if (config('lorekeeper.claymores_and_companions.visibility_settings.levels'))
                        <li class="list-group-item"><a href="{{ url('world/levels') }}">Levels</a></li>
                    @endif
                    @if (config('lorekeeper.claymores_and_companions.visibility_settings.character_stats'))
                        <li class="list-group-item"><a href="{{ url('world/character-stats') }}">Character Stats</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ asset('images/inventory.png') }}" alt="Items" />
                    <h5 class="card-title">Items & Companions</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="{{ url('world/item-categories') }}">Item Categories</a></li>
                    <li class="list-group-item"><a href="{{ url('world/items') }}">All Items</a></li>
                    <li class="list-group-item"><a href="{{ url('world/currency-categories') }}">Currency Categories</a></li>
                    <li class="list-group-item"><a href="{{ url('world/currencies') }}">All Currencies</a></li>
                    <li class="list-group-item"><a href="{{ url('world/pet-categories') }}">Pet Categories</a></li>
                    <li class="list-group-item"><a href="{{ url('world/pets') }}">All Pets</a></li>
                    @if (config('lorekeeper.claymores_and_companions.visibility_settings.skills'))
                        <li class="list-group-item"><a href="{{ url('world/skill-categories') }}">Character Skill Categories</a></li>
                        <li class="list-group-item"><a href="{{ url('world/skills') }}">All Character Skills</a></li>
                    @endif
                    @if (config('lorekeeper.claymores_and_companions.visibility_settings.weapons'))
                        <li class="list-group-item"><a href="{{ url('world/weapon-categories') }}">Weapon Categories</a></li>
                        <li class="list-group-item"><a href="{{ url('world/weapons') }}">All Weapons</a></li>
                    @endif
                    @if (config('lorekeeper.claymores_and_companions.visibility_settings.gear'))
                        <li class="list-group-item"><a href="{{ url('world/gear-categories') }}">Gear Categories</a></li>
                        <li class="list-group-item"><a href="{{ url('world/gear') }}">All Gear</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
@endsection
