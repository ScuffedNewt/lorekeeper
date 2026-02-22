@extends('world.layout')

@section('title')
    Home
@endsection

@section('content')
    {!! breadcrumbs(['Encyclopedia' => 'world', 'Levels' => 'levels']) !!}

    <h1>World</h1>

    <div class="card">
        <div class="card-body text-center">
            <img src="{{ asset('images/account.png') }}" />
            <h5 class="card-title">Levels</h5>
        </div>
        <ul class="list-group list-group-flush">
            @if (config('lorekeeper.claymores_and_companions.visibility_settings.user_levels'))
                <li class="list-group-item"><a href="{{ url('world/levels/user') }}">User Levels</a></li>
            @endif
            @if (config('lorekeeper.claymores_and_companions.visibility_settings.character_levels'))
                <li class="list-group-item"><a href="{{ url('world/levels/character') }}">Character Levels</a></li>
            @endif
        </ul>
    </div>
@endsection
