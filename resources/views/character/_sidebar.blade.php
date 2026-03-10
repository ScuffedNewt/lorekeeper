<ul>
    <li class="sidebar-header"><a href="{{ $character->url }}" class="card-link">{{ $character->slug }}</a></li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Character</div>
        <div class="sidebar-item"><a href="{{ $character->url }}" class="{{ set_active('character/' . $character->slug) }}">Information</a></div>
        <div class="sidebar-item"><a href="{{ $character->url . '/profile' }}" class="{{ set_active('character/' . $character->slug . '/profile') }}">Profile</a></div>
        <div class="sidebar-item"><a href="{{ $character->url . '/gallery' }}" class="{{ set_active('character/' . $character->slug . '/gallery') }}">Gallery</a></div>
        <div class="sidebar-item"><a href="{{ $character->url . '/pets' }}" class="{{ set_active('character/' . $character->slug . '/pets') }}">Pets</a></div>
        <div class="sidebar-item"><a href="{{ $character->url . '/inventory' }}" class="{{ set_active('character/' . $character->slug . '/inventory') }}">Inventory</a></div>
        <div class="sidebar-item"><a href="{{ $character->url . '/bank' }}" class="{{ set_active('character/' . $character->slug . '/bank') }}">Bank</a></div>
        {{-- not implemented
        @if (config('lorekeeper.claymores_and_companions.visibility_settings.gear') || config('lorekeeper.claymores_and_companions.visibility_settings.weapons'))
            <div class="sidebar-item"><a href="{{ $character->url . '/equipment' }}" class="{{ set_active('character/' . $character->slug . '/equipment') }}">Equipment</a></div>
        @endif
        --}}
        @if (config('lorekeeper.claymores_and_companions.visibility_settings.character_levels') || config('lorekeeper.claymores_and_companions.visibility_settings.character_stats'))
            <div class="sidebar-item"><a href="{{ $character->url . '/stats' }}" class="{{ set_active('character/' . $character->slug . '/stats') }}">Stats</a></div>
        @endif
        <div class="sidebar-item"><a href="{{ $character->url . '/status-effects' }}" class="{{ set_active('character/' . $character->slug . '/status-effects') }}">Status Effects</a></div>
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">History</div>
        <div class="sidebar-item"><a href="{{ $character->url . '/images' }}" class="{{ set_active('character/' . $character->slug . '/images') }}">Images</a></div>
        <div class="sidebar-item"><a href="{{ $character->url . '/change-log' }}" class="{{ set_active('character/' . $character->slug . '/change-log') }}">Change Log</a></div>
        <div class="sidebar-item"><a href="{{ $character->url . '/ownership' }}" class="{{ set_active('character/' . $character->slug . '/ownership') }}">Ownership History</a></div>
        <div class="sidebar-item"><a href="{{ $character->url . '/item-logs' }}" class="{{ set_active('character/' . $character->slug . '/item-logs') }}">Item Logs</a></div>
        <div class="sidebar-item"><a href="{{ $character->url . '/currency-logs' }}" class="{{ set_active('character/' . $character->slug . '/currency-logs') }}">Currency Logs</a></div>
        @if (config('lorekeeper.claymores_and_companions.visibility_settings.character_levels') || config('lorekeeper.claymores_and_companions.visibility_settings.character_stats'))
            <div class="sidebar-item"><a href="{{ $character->url . '/stats/logs' }}" class="{{ set_active('character/' . $character->slug . '/stats/logs') }}">Stat Logs</a></div>
        @endif
        @if (config('lorekeeper.claymores_and_companions.visibility_settings.character_skills'))
            <div class="sidebar-item"><a href="{{ $character->url . '/skill-logs' }}" class="{{ set_active('character/' . $character->slug . '/skill-logs') }}">Skill Logs</a></div>
        @endif
        <div class="sidebar-item"><a href="{{ $character->url . '/status-effect-logs' }}" class="{{ set_active('character/' . $character->slug . '/status-effect-logs') }}">Status Effect Logs</a></div>
        <div class="sidebar-item"><a href="{{ $character->url . '/submissions' }}" class="{{ set_active('character/' . $character->slug . '/submissions') }}">Submissions</a></div>
    </li>
    @if (Auth::check() && (Auth::user()->id == $character->user_id || Auth::user()->hasPower('manage_characters')))
        <li class="sidebar-section">
            <div class="sidebar-section-header">Settings</div>
            <div class="sidebar-item"><a href="{{ $character->url . '/profile/edit' }}" class="{{ set_active('character/' . $character->slug . '/profile/edit') }}">Edit Profile</a></div>
            <div class="sidebar-item"><a href="{{ $character->url . '/transfer' }}" class="{{ set_active('character/' . $character->slug . '/transfer') }}">Transfer</a></div>
            @if (Auth::user()->id == $character->user_id)
                <div class="sidebar-item"><a href="{{ $character->url . '/approval' }}" class="{{ set_active('character/' . $character->slug . '/approval') }}">Update Design</a></div>
            @endif
        </li>
    @endif
</ul>
