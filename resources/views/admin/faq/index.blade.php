@extends('admin.layout')

@section('admin-title') Questions @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Questions' => 'admin/faq']) !!}

<h1>Questions</h1>

<p>This is a list of questions in the FAQ. Specific details about questions can be added (e.g tags and categories).</p>

<div class="text-right mb-3">
    <a class="btn btn-primary" href="{{ url('admin/faq/question-categories') }}"><i class="fas fa-folder"></i> FAQ Categories</a>
    <a class="btn btn-primary" href="{{ url('admin/faq/question-tags') }}"><i class="fas fa-tag"></i> FAQ Tags</a>
    <a class="btn btn-primary" href="{{ url('admin/faq/question/create') }}"><i class="fas fa-plus"></i> Create New Question</a>
</div>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('question_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($questions))
    <p>No questions found.</p>
@else
    {!! $questions->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-5 col-md-6"><div class="logs-table-cell">Question</div></div>
                <div class="col-5 col-md-5"><div class="logs-table-cell">Category | Tags</div></div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach($questions as $question)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-5 col-md-6"><div class="logs-table-cell">{{ $question->name }}</div></div>
                        <div class="col-4 col-md-5"><div class="logs-table-cell">{{ $question->category ? $question->category->name : '' }}</div></div>
                        <div class="col-3 col-md-1 text-right">
                            <div class="logs-table-cell">
                                <a href="{{ url('admin/data/questions/edit/'.$question->id) }}"  class="btn btn-primary py-0 px-2">Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {!! $questions->render() !!}
@endif
@endsection
