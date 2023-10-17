@extends('layouts.app')

@section('title')
    IP Blocked
@endsection

@section('content')
    <h3 class="text-danger">You are not Permitted to access this site.</h3>
    <h5>The IP you are currently on is banned from accessing this website.</h5>
    <p>If this is a mistake, please try login. If the issue persists please reach out to us.</p>
    <br>
    <p>Current IP: {{ $ip }}</p>
@endsection
