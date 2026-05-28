@if ($readOnly && $user->id != (Auth::check() ? Auth::user()->id : null))
    @if (Auth::check() && config('lorekeeper.garden.allow_users_to_water_gardens'))
        <div class="guest-watering-alert alert alert-info">
            You can water plants in gardens you visit to help them grow!
            @if (config('lorekeeper.garden.user_guest_water_limit'))
                You have <strong class="guest-waters-remaining">{{ max(0, config('lorekeeper.garden.user_guest_water_limit') - (Auth::user()?->activity?->garden_guest_waters_today ?? 0)) }}</strong> waters remaining today.
            @endif
        </div>
    @endif
@endif

<div class="container">
    <div class="garden-card">
        <div class="garden-header">
            <ul class="nav nav-tabs card-header-tabs garden-tabs" id="gardenTabs">
                @foreach ($groups as $group => $gardens)
                    @foreach ($gardens as $garden)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first && $group == 'unlocked' ? 'active' : '' }}" id="gardenTab-{{ $garden->id }}" data-toggle="tab" href="#garden-{{ $garden->id }}" role="tab">
                                @if ($garden->has_image)
                                    <img src="{{ $garden->imageUrl }}" style="max-height: 30px;">
                                @endif
                                {!! $isGardenUnlocked[$garden->id] ? '' : '<i class="fas fa-lock"></i> ' !!}
                                {{ $garden->name }}
                            </a>
                        </li>
                    @endforeach
                @endforeach
            </ul>
            <div class="mode-pills btn-group btn-group-toggle" data-toggle="buttons" role="group" aria-label="Garden modes">
                @if ($readOnly)
                    <label class="pill btn active" data-mode="tend">
                        <input type="radio" name="mode" autocomplete="off" checked> Tend
                    </label>
                @else
                    <label class="pill btn active" data-mode="place">
                        <input type="radio" name="mode" autocomplete="off" checked> Place
                    </label>
                    <label class="pill btn" data-mode="tend">
                        <input type="radio" name="mode" autocomplete="off"> Tend
                    </label>
                    <label class="pill btn" data-mode="collect">
                        <input type="radio" name="mode" autocomplete="off"> Collect
                    </label>
                @endif
            </div>
        </div>
        <div class="card-body tab-content">
            @foreach ($groups as $group => $gardens)
                @foreach ($gardens as $garden)
                    <div class="tab-pane fade {{ $loop->first && $group == 'unlocked' ? 'show active' : '' }}" id="garden-{{ $garden->id }}" role="tabpanel">
                        @if ($isGardenUnlocked[$garden->id])
                            @include('gardens._panel', [
                                'user' => $user,
                                'garden' => $garden,
                                'readOnly' => $readOnly,
                                'modifiers' => $readOnly ? [] : $modifierUserObjects[$garden->id] ?? [],
                                'placeables' => $readOnly ? [] : $placeableUserObjects[$garden->id] ?? [],
                            ])
                        @else
                            <div class="alert alert-danger">This garden is locked.</div>
                            @if (!$readOnly)
                                @include('widgets._limits', ['object' => $garden])
                            @endif
                        @endif
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
</div>
