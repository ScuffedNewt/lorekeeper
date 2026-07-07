@extends('admin.layout')

@section('admin-title')
    Frequently Asked Questions
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Faq' => 'admin/data/faq', ($faq->id ? 'Edit' : 'Create') . ' Faq' => $faq->id ? 'admin/data/faq/edit/' . $faq->id : 'admin/data/faq/create']) !!}

    <h1>{{ $faq->id ? 'Edit' : 'Create' }} Faq
        @if ($faq->id)
            <a href="#" class="btn btn-outline-danger float-right delete-faq-button">Delete Question</a>
        @endif
    </h1>

    {!! Form::open(['url' => $faq->id ? 'admin/data/faq/edit/' . $faq->id : 'admin/data/faq/create']) !!}

    <div class="form-group">
        {!! Form::label('Question') !!}
        {!! Form::text('question', $faq->question, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Answer') !!}
        {!! Form::textarea('answer', $faq->answer, ['class' => 'form-control wysiwyg']) !!}
    </div>

    {{-- tags --}}
    <div class="form-group">
        {!! Form::label('Categories / Tags') !!}
        {!! Form::select('tags[]', $tags, $faq->tags, ['class' => 'form-control', 'multiple', 'id' => 'tags', 'placeholder' => 'Select Categories']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $faq->is_visible ?? 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit($faq->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($faq->id)
        <h3>Preview</h3>
        @include('browse._faq_question', ['faq' => $faq])
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();

            $('#tags').selectize({
                maxFaq: 10
            });

            $('.delete-faq-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/faq/delete') }}/{{ $faq->id }}", 'Delete Question');
            });
        });
    </script>
@endsection
