@extends('world.layout')

@section('title')
    Awards
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Awards' => 'world/awards']) !!}
    <h1>Awards</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('award_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'alpha' => 'Sort Alphabetically (A-Z)',
                        'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                        'category' => 'Sort by Category',
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                    ],
                    Request::get('sort') ?: 'alpha',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'ownership',
                    [
                        'default' => 'Any Attachment',
                        'all' => 'Attaches to All',
                        'character' => 'Only Attaches to Characters',
                        'user' => 'Only Attaches to Users',
                    ],
                    Request::get('ownership') ?: 'default',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $awards->render() !!}
    <div class="row">
        @foreach ($awards as $award)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    @if ($award->has_image)
                        <div class="card-header text-center">
                            <a href="{{ $award->idUrl }}">
                                <img src="{{ $award->imageUrl }}" class="img-fluid">
                            </a>
                        </div>
                    @endif
                    <div class="card-body text-center">
                        <h5 class="mt-3">{!! $award->displayName !!}</h5>
                        <div>
                            @if ($award->is_character_owned)
                                <i class="fas fa-paw mx-2" data-toggle="tooltip" title="This award can be held by characters."></i>
                            @endif
                            @if ($award->is_user_owned)
                                <i class="fas fa-user mx-2" data-toggle="tooltip" title="This award can be held by users."></i>
                            @endif
                        </div>
                        @if (isset($award->category) && $award->category)
                            {!! $award->category->displayName !!}
                        @endif
                        @if (isset($award->rarity) && isset($award->category))
                            |
                        @endif
                        @if (isset($award->rarity) && $award->rarity)
                            Rarity: {!! $award->rarity->displayName !!}
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    {!! $awards->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $awards->total() }} award{{ $awards->total() == 1 ? '' : 's' }} found.</div>
@endsection
