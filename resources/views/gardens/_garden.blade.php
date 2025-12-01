@if ($readOnly && !$user->id == (Auth::check() ? Auth::user()->id : null))
    @if (Auth::check() && config('lorekeeper.garden.allow_users_to_water_gardens'))
        <div class="alert alert-info">
            You can water plants in gardens you visit to help them grow!
            @if (config('lorekeeper.garden.user_guest_water_limit'))
                You have <strong>{{ config('lorekeeper.garden.user_guest_water_limit') - $user->activity?->garden_guest_waters_today }}</strong> waters remaining today.
            @endif
        </div>
    @endif
@endif

<div>
    <div class="card character-bio">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                @foreach ($groups as $group => $gardens)
                    @foreach ($gardens as $garden)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first && $group == 'unlocked' ? 'active' : '' }}" id="gardenTab-{{ $garden->id }}" data-toggle="tab" href="#{{ strtolower(str_replace(' ', '-', $garden->name)) }}" role="tab">
                                {!! $is_garden_unlocked[$garden->id] ? '' : '<i class="fas fa-lock"></i> ' !!}
                                @if ($garden->has_image)
                                    <img src="{{ $garden->imageUrl }}" class="img-fluid" style="max-height: 3em;">
                                @endif
                                {{ $garden->name }}
                            </a>
                        </li>
                    @endforeach
                @endforeach
            </ul>
        </div>
        <div class="card-body tab-content">
            @foreach ($groups as $group => $gardens)
                @foreach ($gardens as $garden)
                    <div class="tab-pane fade {{ $loop->first && $group == 'unlocked' ? 'show active' : '' }}" id="{{ strtolower(str_replace(' ', '-', $garden->name)) }}">
                        @if ($is_garden_unlocked[$garden->id])
                            @include('gardens._panel', [
                                'garden'              => $garden,
                                'user_garden_objects' => $readOnly ? [] : $garden->getUserGardenObjects($user),
                                'user'                => $user,
                                'readOnly'            => $readOnly,
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