@extends('user.layout')

@section('profile-title') Transfer Request (#{{ $transfer->id }}) @endsection

@section('profile-content')
{!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Transfer Request (#' . $transfer->id . ')' => $transfer->viewUrl]) !!}

@include('home._transfer_content', ['transfer' => $transfer])

@endsection
