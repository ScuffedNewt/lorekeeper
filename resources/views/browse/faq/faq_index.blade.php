@extends('layouts.app')

@section('title') FAQ @endsection

@section('content')
{!! breadcrumbs(['FAQ' => 'faq']) !!}
<div class="text-center">
    <h1>
        FAQ
    </h1>
    <p>The FAQ contains a list of frequently asked questions. If you have a question that is not listed here, please consider submitting a question to be answered.</p>
    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'text-center justify-content-center mx-5']) !!}
            <div class="form-group mr-3 mb-3 mx-5">
                {!! Form::text('query', Request::get('query'), ['class' => 'form-control']) !!}
            </div>
            <div class="form-group mr-3 mb-3 mx-5">
                {!! Form::select('sort', [
                    'alpha'          => 'Sort Alphabetically (A-Z)',
                    'alpha-reverse'  => 'Sort Alphabetically (Z-A)',
                    'alias'          => 'Sort by Alias (A-Z)',
                    'alias-reverse'  => 'Sort by Alias (Z-A)',
                    'rank'           => 'Sort by Rank (Default)',
                    'newest'         => 'Newest First',
                    'oldest'         => 'Oldest First'    
                ], Request::get('sort') ? : 'category', ['class' => 'form-control']) !!}
            </div>
            <div class="form-group mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        {!! Form::close() !!}
    </div>

    {!! $questions->render() !!}
        @foreach($questions as $question)
            test<br><br>
        @endforeach
    {!! $questions->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $questions->total() }} result{{ $questions->total() == 1 ? '' : 's' }} found.</div>
</div>
@endsection
