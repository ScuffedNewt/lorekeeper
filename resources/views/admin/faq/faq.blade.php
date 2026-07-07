@extends('admin.layout')

@section('admin-title')
    Frequently Asked Questions
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'FAQ' => 'admin/data/faq']) !!}

    <h1>FAQ</h1>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/faq/create') }}"><i class="fas fa-plus"></i> Create New Question</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'text-right justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('content', Request::get('content'), ['class' => 'form-control col-md-6 ml-auto', 'placeholder' => 'Question or Answer Content']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('tags[]', $tags, Request::get('tags'), ['class' => 'form-control selectize col-md-6 ml-auto', 'multiple', 'placeholder' => 'Select Tags']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($faqs))
        <p>No questions / answers found.</p>
    @else
        {!! $faqs->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-4 col-md-4">
                        <div class="logs-table-cell">Question</div>
                    </div>
                    <div class="col-5 col-md-5">
                        <div class="logs-table-cell">Answer</div>
                    </div>
                    <div class="col-2 col-md-2">
                        <div class="logs-table-cell">Categories / Tags</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($faqs as $faq)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-4 col-md-4">
                                <div class="logs-table-cell">
                                    @if (!$faq->is_visible)
                                        <i class="fas fa-eye-slash mr-1"></i>
                                    @endif
                                    {{ Str::words($faq->question, 5, '...') }}
                                </div>
                            </div>
                            <div class="col-5 col-md-5">
                                <div class="logs-table-cell">
                                    {!! Str::words($faq->answer, 10, '...') !!}
                                </div>
                            </div>
                            <div class="col-2 col-md-2">
                                <div class="logs-table-cell">
                                    @if ($faq->tags)
                                        {{ implode(', ', $faq->tags) }}
                                    @endif
                                </div>
                            </div>
                            <div class="col col-md-1 text-right">
                                <div class="logs-table-cell">
                                    <a href="{{ url('admin/data/faq/edit/' . $faq->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $faqs->render() !!}
    @endif
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();
        });
    </script>
@endsection
