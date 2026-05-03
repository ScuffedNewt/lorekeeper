@extends('admin.layout')

@section('admin-title')
    Awards
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Awards' => 'admin/data/awards']) !!}

    <h1>Awards</h1>

    <p>This is a list of Awards in the game. Awards can be granted via prompts, claims, or admin grants. Awards can also be set to be held by characters, users, or both.</p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/award-categories') }}"><i class="fas fa-folder"></i> Award Categories</a>
        <a class="btn btn-primary" href="{{ url('admin/data/awards/create') }}"><i class="fas fa-plus"></i> Create New Award</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('award_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($awards))
        <p>No Awards found.</p>
    @else
        {!! $awards->render() !!}

        <div class="row ml-md-2 mb-4">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                <div class="col-5 col-md-6 font-weight-bold">Name</div>
                <div class="col-5 col-md-5 font-weight-bold">Category</div>
            </div>
            @foreach ($awards as $award)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-5 col-md-6">
                        @if ($award->has_image)
                            <div class="d-flex align-items-center">
                                <img src="{{ $award->image_url }}" class="mr-2" style="max-height: 50px; max-width: 50px;" />
                                {{ $award->name }}
                            </div>
                        @else
                            {{ $award->name }}
                        @endif
                    </div>
                    <div class="col-4 col-md-5"> {{ $award->category ? $award->category->name : 'None' }} </div>
                    <div class="col-3 col-md-1 text-right">
                        <a href="{{ url('admin/data/awards/edit/' . $award->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>

        {!! $awards->render() !!}
    @endif

@endsection

@section('scripts')
    @parent
@endsection
