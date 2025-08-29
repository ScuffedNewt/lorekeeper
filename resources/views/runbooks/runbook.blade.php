@extends('layouts.app')

@section('title')
    {{ $runbook->title }}
@endsection

@if ($runbook->has_image)
    @section('meta-img')
        {{ $runbook->imageUrl }}
    @endsection
@endif

@section('content')
    <x-admin-edit title="Runbook" :object="$runbook" />
    {!! breadcrumbs([$runbook->title => $runbook->url]) !!}
    <h1>
        @if (!$runbook->is_public)
            <i class="fas fa-eye-slash mr-1"></i>
        @endif
        {{ $runbook->title }}
    </h1>
    <div class="mb-4">
        <div><strong>Created:</strong> {!! format_date($runbook->created_at) !!}</div>
        <div><strong>Last updated:</strong> {!! format_date($runbook->updated_at) !!}</div>
    </div>

    <div class="site-runbook-content parsed-text">
        {!! parseRunbooks($runbook->text) !!}
    </div>

    @if ($runbook->can_comment)
        <div class="container">
            @comments([
                'model' => $runbook,
                'perRunbook' => 5,
                'allow_dislikes' => $runbook->allow_dislikes,
            ])
        </div>
    @endif
@endsection
