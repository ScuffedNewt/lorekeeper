@extends('layouts.app')

@section('title')
    IP Blocked
@endsection

@section('content')
    <h3 class="text-danger">Registration is not permitted from this IP address.</h3>
    <p>You may still <a href="{{ route('login') }}">log in</a> to an existing account. If you believe this is a mistake, please contact us.</p>
    <br>
    <p>Current IP: {{ $ip }}</p>
@endsection
