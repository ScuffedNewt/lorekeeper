@if (Settings::get('garden_view'))
    <div class="text-right mb-3">
        <div class="btn-group">
            <button type="button" class="btn btn-secondary active nav-view-button" data-toggle="tooltip" title="Nav View" alt="Nav View">
                <i class="fas fa-bars"></i>
            </button>
            <button type="button" class="btn btn-secondary grid-view-button" data-toggle="tooltip" title="Grid View" alt="Grid View">
                <i class="fas fa-th"></i>
            </button>
        </div>
    </div>
@endif

@if ($readOnly)
    @if (config('lorekeeper.garden.allow_users_to_water_gardens'))
        <div class="alert alert-info">
            You can water plants in gardens you visit to help them grow!
            @if (config('lorekeeper.garden.user_guest_water_limit'))
                You have <strong>{{ config('lorekeeper.garden.user_guest_water_limit') - $user->activity?->garden_guest_waters_today }}</strong> waters remaining today.
            @endif
        </div>
    @endif
    @include('gardens._nav', [
        'readOnly'           => $readOnly,
        'user'               => $user,
        'groups'             => $groups,
        'is_garden_unlocked' => $is_garden_unlocked,
    ])
@else
    {{-- Only non-readonly has grid view --}}
    @include('gardens._nav', [
        'user'               => $user,
        'groups'             => $groups,
        'is_garden_unlocked' => $is_garden_unlocked,
    ])
    @include('gardens._grid', [
        'groups'             => $groups,
    ])
@endif

