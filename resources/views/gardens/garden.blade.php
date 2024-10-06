@extends('layouts.app')

@section('title')
    {{ $garden->name }}
@endsection

@section('content')

    {!! breadcrumbs(['Home' => '/', 'Gardens' => 'gardens', $garden->name => 'gardens/' . $garden->id]) !!}

    <h1>{{ $garden->name }}</h1>

    <div>
        <img src="{{ $garden->backgroundImageUrl }}" class="img-fluid">
    </div>
    <div>
        {!! $garden->description !!}
    </div>

@endsection