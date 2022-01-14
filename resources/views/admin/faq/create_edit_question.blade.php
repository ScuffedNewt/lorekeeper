@extends('admin.layout')

@section('admin-title') Questions @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Questions' => 'admin/data/questions', ($question->id ? 'Edit' : 'Create').' Question' => $question->id ? 'admin/data/questions/edit/'.$question->id : 'admin/data/questions/create']) !!}

<h1>{{ $question->id ? 'Edit' : 'Create' }} Question
    @if($question->id)
        <a href="#" class="btn btn-outline-danger float-right delete-question-button">Delete Question</a>
    @endif
</h1>

{!! Form::open(['url' => $question->id ? 'admin/data/questions/edit/'.$question->id : 'admin/data/questions/create', 'files' => true]) !!}

<h3>FAQ Question</h3>

<div class="row">
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('Question') !!} {!! add_help('This should be a number.') !!}
            {!! Form::text('question', $question->question, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('Answer') !!}
            {!! Form::text('answer', $question->answer, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('Question Category (Optional)') !!}
    {!! Form::select('faq_category_id', $categories, $question->question_category_id, ['class' => 'form-control']) !!}
</div>

<div class="text-right">
    {!! Form::submit($question->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endif

@endsection

@section('scripts')
@parent
<script>


</script>
@endsection
