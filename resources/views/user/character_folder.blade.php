@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s {{ $folder->name }} Characters
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Characters' => $user->url . '/characters', $folder->name => '/' . $folder->name]) !!}

    <h1>
        {!! $user->displayName !!}'s {{ $folder->name }} Characters
    </h1>

    @if ($characters->count())
        <div class="card mb-3 inventory-category">
            <a href="{{ $folder->url }}">
                <h5 class="card-header inventory-header">
                    <span data-toggle="tooltip" title="{{ $folder->description }}">{{ $folder->name }}</span>
                </h5>
            </a>

            <div class="card-body inventory-body">
                <div class="row mb-2">
                    @foreach ($characters as $character)
                        <div class="col-md-3 col-6 text-center mb-2">
                            <div>
                                <a href="{{ $character->url }}"><img src="{{ $character->image->thumbnailUrl }}" class="img-thumbnail" alt="Thumbnail for {{ $character->fullName }}" /></a>
                            </div>
                            <div class="mt-1 h5">
                                @if (!$character->is_visible)
                                    <i class="fas fa-eye-slash"></i>
                                @endif {!! $character->displayName !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <p>No characters found.</p>
    @endif

@endsection
