@extends('galleries.layout')

@section('gallery-title')
    Oekaki
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('chickenpaint/chickenpaint.css') }}">
    <style>
        body {
            -webkit-user-select: none;
            /* For iOS Safari: Prevent long-press from popping up a selection dialog on body text */
            font-family: sans-serif;
        }
    </style>
@endpush

@push('head')
    <script src="{{ asset('chickenpaint/chickenpaint.js') }}"></script>
    <meta name="viewport" content="width=device-width,user-scalable=no">
@endpush

@section('gallery-content')
    {!! breadcrumbs(['Oekaki' => 'oekaki']) !!}

    <h1>Oekaki</h1>
    <p>Draw your own art here! You can save your work as a PNG / ACO / CHI (if there are layers) file, or post it into the designated gallery.</p>
    <p>
        If you would like to load artwork, simply upload a .chi or .png file here.
        <br><b>Note: uploaded artwork is saved to the server for 5 minutes. You will have to save to your device or post to the gallery to keep progress beyond initial upload.</b>
    </p>

    {!! Form::open(['url' => 'oekaki/save', 'files' => true]) !!}

    {!! Form::label('file', 'Load a .chi / .png file') !!}

    <div class="row ml-3">
        <div class="form-group">
            {!! Form::file('file', ['class' => 'form-control']) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::submit('Load', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>

    {!! Form::close() !!}

    <div id="oekaki"></div>
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new ChickenPaint({
                uiElem: document.getElementById("oekaki"),

                @if (Settings::get('oekaki_gallery_id'))
                    // this naming is confusing,
                    // saving = posting to gallery, post = redirect
                    saveUrl: "{{ url('oekaki/publish') }}",
                    postUrl: @if (Settings::get('oekaki_gallery_auto_approve'))
                        "{{ url('gallery') }}/".Settings::get('oekaki_gallery_id'),
                    @else
                        "{{ url('gallery/submissions/pending') }}",
                    @endif
                @endif
                @if (isset($url))
                    loadImageUrl: "{{ $url }}",
                @endif

                allowMultipleSends: true,
                allowDownload: true,
                disableBootstrapAPI: true,
                fullScreenMode: "auto",
            });
        });
    </script>
@endsection
