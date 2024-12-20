@extends('layouts.app')

@section('title')
    Dailies ::
    @yield('dailies-title')
@endsection

@section('sidebar')
    @include('dailies._sidebar')
@endsection

@section('content')
    @yield('dailies-content')
@endsection

@section('scripts')
    @parent
@endsection
