@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Armoury
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Armoury' => $user->url . '/armoury']) !!}

    <h1>
        Armoury
    </h1>

    @if (config('lorekeeper.claymores_and_companions.visibility_settings.weapons'))
        <h2>Weapons</h2>
        @foreach ($weapons as $categoryId => $categoryWeapons)
            <div class="card mb-3 inventory-category">
                <h5 class="card-header inventory-header">
                    {!! isset($weaponCategories[$categoryId]) ? '<a href="' . $weaponCategories[$categoryId]->searchUrl . '">' . $weaponCategories[$categoryId]->name . '</a>' : 'Miscellaneous' !!}
                </h5>
                <div class="card-body inventory-body">
                    @foreach ($categoryWeapons->chunk(4) as $chunk)
                        <div class="row mb-3">
                            @foreach ($chunk as $weapon)
                                <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $weapon->pivot->id }}" data-name="{{ $user->name }}'s {{ $weapon->name }}">
                                    <div class="mb-1">
                                        <a href="#" class="weapon-stack">
                                            @if ($weapon->pivot->has_image)
                                                <img class="rounded" src="{{ $weapon->getStackImageUrl($weapon->pivot->id) }}" data-toggle="tooltip" title="{{ $weapon->name }}" />
                                            @elseif($weapon->imageUrl)
                                                <img class="rounded" src="{{ $weapon->imageUrl }}" data-toggle="tooltip" title="{{ $weapon->name }}" />
                                            @endif
                                        </a>
                                    </div>
                                    <div>
                                        <a href="#" class="weapon-stack inventory-stack-name">{{ $weapon->name }}</a>
                                        @if ($weapon->pivot->character_id)
                                            <p class="small">
                                                Attached to {!! getDisplayName(\App\Models\Character\Character::class, $weapon->pivot->character_id) !!}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    <hr class="my-4">

    @if (config('lorekeeper.claymores_and_companions.visibility_settings.gear'))
        <h2>Gear</h2>
        @foreach ($gears as $categoryId => $categoryGears)
            <div class="card mb-3 inventory-category">
                <h5 class="card-header inventory-header">
                    {!! isset($gearCategories[$categoryId]) ? '<a href="' . $gearCategories[$categoryId]->searchUrl . '">' . $gearCategories[$categoryId]->name . '</a>' : 'Miscellaneous' !!}
                </h5>
                <div class="card-body inventory-body">
                    @foreach ($categoryGears->chunk(4) as $chunk)
                        <div class="row mb-3">
                            @foreach ($chunk as $gear)
                                <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $gear->pivot->id }}" data-name="{{ $user->name }}'s {{ $gear->name }}">
                                    <div class="mb-1">
                                        <a href="#" class="gear-stack">
                                            @if ($gear->pivot->has_image)
                                            <img src="{{ url('images/data/user-gears/' . $gear->pivot->id . '-image.png') }}">@else<img src="{{ $gear->imageUrl }}" />
                                            @endif
                                        </a>
                                    </div>
                                    <div>
                                        <a href="#" class="gear-stack inventory-stack-name">{{ $gear->name }}</a>
                                        @if ($gear->pivot->character_id)
                                            <p class="small">Attached to {!! getDisplayName(\App\Models\Character\Character::class, $gear->pivot->character_id) !!}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    <h3>Latest Activity</h3>

    @if (config('lorekeeper.claymores_and_companions.visibility_settings.weapons'))
        <h5>Weapon</h5>
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Sender</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Recipient</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Weapon</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="logs-table-cell">Log</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Date</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($weaponLogs as $log)
                    <div class="logs-table-row">
                        @include('user._weapon_log_row', ['log' => $log, 'owner' => $user])
                    </div>
                @endforeach
            </div>
        </div>
        <div class="text-right">
            <a href="{{ url($user->url . '/weapon-logs') }}">View all...</a>
        </div>
    @endif

    @if (config('lorekeeper.claymores_and_companions.visibility_settings.weapons') && config('lorekeeper.claymores_and_companions.visibility_settings.gear'))
        <hr class="my-4">
    @endif

    @if (config('lorekeeper.claymores_and_companions.visibility_settings.gear'))
        <h5>Gear</h5>
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Sender</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Recipient</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Gear</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="logs-table-cell">Log</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Date</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($gearLogs as $log)
                    <div class="logs-table-row">
                        @include('user._gear_log_row', ['log' => $log, 'owner' => $user])
                    </div>
                @endforeach
            </div>
        </div>
        <div class="text-right">
            <a href="{{ url($user->url . '/gear-logs') }}">View all...</a>
        </div>
    @endif

@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('.weapon-stack').on('click', function(e) {
                e.preventDefault();
                var $parent = $(this).parent().parent();
                loadModal("{{ url('weapons') }}/" + $parent.data('id'), $parent.data('name'));
            });
            $('.gear-stack').on('click', function(e) {
                e.preventDefault();
                var $parent = $(this).parent().parent();
                loadModal("{{ url('gear') }}/" + $parent.data('id'), $parent.data('name'));
            });
        });
    </script>
@endsection
