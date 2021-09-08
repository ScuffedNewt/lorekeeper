@extends('prompts.layout')

@section('title') Prompts - {{$prompt->name}} @endsection

@section('content')
{!! breadcrumbs(['Prompts' => 'prompts', 'All Prompts' => 'prompts/prompts', $prompt->name => 'prompts/prompts/' . $prompt->id]) !!}

<div class="card mb-3">
    <div class="card-body">
        @include('prompts._prompt_entry', ['prompt' => $prompt])
    </div>
</div>

@endsection
