<ul>
    <li class="sidebar-header"><a href="{{ url(__('dailies.dailies')) }}" class="card-link">{{ __('dailies.dailies') }}</a></li>

    <li class="sidebar-section">
        @foreach ($dailies as $daily)
            <div class="sidebar-item"><a href="{{ $daily->url }}" class="{{ set_active('dailies/' . $daily->id) }}">{{ $daily->name }}</a></div>
        @endforeach
    </li>
</ul>
