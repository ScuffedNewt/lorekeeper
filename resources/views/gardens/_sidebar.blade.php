<ul>
    <li class="sidebar-header">
        <a href="{{ url('gardens') }}" class="card-link">Gardens</a>
    </li>

    <li class="sidebar-section">
        <div class="sidebar-section-header">Garden</div>
        <div class="sidebar-item">
            <a href="{{ url('gardens') }}" class="{{ set_active('gardens') }}">Manage Garden</a>
        </div>
        @if (Auth::check())
            <div class="sidebar-item">
                <a href="{{ Auth::user()->url.'/garden' }}">My Garden</a>
            </div>
        @endif
        <div class="sidebar-item">
            <a href="{{ url('world/gardens') }}">Garden Encyclopedia</a>
        </div>
    </li>

    @foreach ($groups ?? [] as $group => $gardens)
        @if ($gardens->count())
            <li class="sidebar-section">
                <div class="sidebar-section-header">{{ $group === 'unlocked' ? 'My Gardens' : 'Locked Gardens' }}</div>
                @foreach ($gardens as $garden)
                    <div class="sidebar-item">
                        <a href="#garden-{{ $garden->id }}" data-garden-tab="gardenTab-{{ $garden->id }}">
                            @if (!($isGardenUnlocked[$garden->id] ?? false))
                                <i class="fas fa-lock fa-fw" aria-hidden="true"></i>
                            @endif
                            {{ $garden->name }}
                        </a>
                    </div>
                @endforeach
            </li>
        @endif
    @endforeach
</ul>
