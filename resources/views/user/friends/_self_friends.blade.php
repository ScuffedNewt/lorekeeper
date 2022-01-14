@extends('home.layout')

@section('home-title') Friends @endsection

@section('home-content')
{!! breadcrumbs(['Friends' => 'Friends']) !!}

    {{-- Pending Friends --}}
    @include('user.friends._pending_friends', ['friends' => $friends->where('recipient_approval', 'Pending'), 'user' => $user])

    {{-- Friends --}}
    @include('user.friends._friends', ['friends' => $friends->where('recipient_approval', 'Accepted'), 'user' => $user])

@endsection
