@if (isset($testMyos))
    <h5>
        Pairing of:
        <a href="{{ url('character/' . $pairing_characters[0]) }}">
            {{ $pairing_characters[0] }}
        </a>
        &
        <a href="{{ url('character/' . $pairing_characters[1]) }}">
            {{ $pairing_characters[1] }}
        </a>
    </h5>
    <div class="row">
        @foreach ($testMyos as $test)
            @include('admin.pairings._pairing_myo', ['myo' => $test])
        @endforeach
    </div>
@else
    <p>No MYOs rolled.</p>
@endif
