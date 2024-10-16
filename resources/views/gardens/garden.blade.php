@extends('layouts.app')

@section('title')
    {{ $garden->name }}
@endsection

@section('content')
    {!! breadcrumbs(['Home' => '/', 'Gardens' => 'gardens', $garden->name => 'gardens/' . $garden->id]) !!}

    <head>
        <link href="{{ asset('css/gardens.css?v=' . filemtime(public_path('css/gardens.css'))) }}" rel="stylesheet">
    </head>

    <h1>{{ $garden->name }}</h1>

    <div>
        <img src="{{ $garden->backgroundImageUrl }}" class="img-fluid">
    </div>
    <div>
        {!! $garden->description !!}
    </div>
@endsection
