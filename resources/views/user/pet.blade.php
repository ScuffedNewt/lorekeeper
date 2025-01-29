@php $namespace = (Request::url() == url('pets/view/'.$pet->id)) && (Auth::check() && Auth::user()->id == $pet->user_id); @endphp
@extends(($namespace ? 'home' : 'user') . '.layout')

@section($namespace ? 'title' : 'profile-title')
    {{ $pet->pet_name ? $pet->pet_name . ' (' . $pet->pet->name . ')' : $user->name . "'s " . $pet->pet->name }}
@endsection

@section($namespace ? 'content' : 'profile-content')

    {!! $namespace
        ? breadcrumbs(['Pets' => 'pets', $pet->pet_name ? $pet->pet_name . ' (' . $pet->pet->name . ')' : $user->name . "'s " . $pet->pet->name => $pet->url])
        : breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Pets' => $user->url . '/pets', $pet->pet_name ? $pet->pet_name . ' (' . $pet->pet->name . ')' : $user->name . "'s " . $pet->pet->name => $pet->url]) !!}

    <h1>
        {!! $pet->pet_name ? $pet->pet_name . ' (' . $pet->pet->name . ')' : $user->name . "'s " . $pet->pet->name . ($pet->pet->isVariant ? ' (' . $pet->pet->parent->name . ')' : '') !!}
    </h1>

    @if (!$namespace)
        <div class="container justify-content-right text-right my-3">
            <a href="{{ $user->url . '/pets' }}">
                <div class="btn btn-primary">Return to Pets</div>
            </a>
        </div>
    @endif

    @if (Auth::check() && ($pet->user_id !== Auth::user()->id && Auth::user()->hasPower('edit_inventories')))
        <div class="alert alert-warning">
            You are editing this pet as a staff member.
        </div>
    @endif

    <div class="row world-entry">
        <div class="col-md-3 world-entry-image">
            <img class="img-fluid rounded mb-2" src="{{ $pet->pet->image($pet->id) }}" data-toggle="tooltip" title="{{ $pet->pet_name ?? $pet->pet->name }}" alt="{{ $pet->pet_name ?? $pet->pet->name }}" />
        </div>
        <div class="col-md-9">
            <div class="row col-12 world-entry-text">
                <div class="col-md-4 mb-2 text-center">
                    @if ($pet->character)
                        <h2 class="h5">Attached to {{ $pet->character->fullName }}</h2>
                        <a href="{{ $pet->character->url }}">
                            <img src="{{ $pet->character->image->thumbnailUrl }}" class="rounded img-thumbnail mb-2" alt="Thumbnail for {{ $pet->character->fullName }}" />
                        </a>
                        @if ($namespace)
                            @if (Auth::check() && Auth::user()->id == $pet->character->user_id && $pet->canBond())
                                <div class="form-group mb-0">
                                    {!! Form::open(['url' => 'pets/bond/' . $pet->id]) !!}
                                    {!! Form::submit('Bond', ['class' => 'btn btn-primary']) !!}
                                    {!! Form::close() !!}
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">{{ $pet->canBond(true) }}</div>
                            @endif
                        @endif
                    @endif
                    @if ($pet->evolution)
                        <h2 class="h5">Evolved</h2>
                        <p>
                            {{ $pet->evolution->evolution_name }} (Stage {{ $pet->evolution->evolution_stage }})
                        </p>
                    @endif
                </div>
                @if ($pet->pet->hasDrops)
                    <div class="col-md-8 mb-2">
                        @include('user._pet_drops', ['pet' => $pet, 'drops' => $pet->drops])
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="pl-2 pr-2 pb-2">
        @if ($pet->has_image)
            <div>
                <p class="alert alert-info">
                    This pet is displaying custom art!
                    @if (isset($pet->petArtist) && $pet->petArtist)
                        <b>Artist:</b> {!! $pet->petArtist !!}
                    @else
                        No credits given.
                    @endif
                </p>

            </div>
        @endif
        @if ($pet->description)
            <hr>
            <div>
                <h2 class="h5">Profile</h2>
                {!! $pet->description !!}
            </div>
        @endif
    </div>
    @if (Auth::check() && ($pet->user_id == Auth::user()->id || Auth::user()->hasPower('edit_inventories')))
        <div class="card">
            <ul class="list-group list-group-flush">
                @include('home._pet_form', ['pet' => $pet, 'user' => Auth::user()])
            </ul>
        </div>
    @endif
@endsection
