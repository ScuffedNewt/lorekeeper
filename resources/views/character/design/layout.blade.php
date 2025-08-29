@extends('layouts.app')

@section('title')
    Design Approvals{!! View::hasSection('design-title') ? ' :: ' . trim(View::getSection('design-title')) : '' !!}
@endsection

@section('sidebar')
    @include('character.design._sidebar')
@endsection

@section('content')
    @if (isset($request) && $request->status != 'Draft' && Auth::user()->isStaff)
        @include('character.design._runbook_modal', ['request' => $request])
    @endif
    @yield('design-content')
@endsection

@section('scripts')
    @parent
@endsection
