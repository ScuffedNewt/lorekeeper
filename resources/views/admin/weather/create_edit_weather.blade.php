@extends('admin.layout')

@section('admin-title') Weather @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Weather' => 'admin/weather/weathers', ($weather->id ? 'Edit' : 'Create').' Weather' => $weather->id ? 'admin/weather/weathers/edit/'.$weather->id : 'admin/weather/weathers/create']) !!}

<h1>{{ $weather->id ? 'Edit' : 'Create' }} Weather
    @if($weather->id)
        <a href="#" class="btn btn-danger float-right delete-weather-button">Delete Weather</a>
    @endif
</h1>

{!! Form::open(['url' => $weather->id ? 'admin/weather/weathers/edit/'.$weather->id : 'admin/weather/weathers/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $weather->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used on the world information pages and side widget.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: 200px x 200px</div>
    @if($weather->has_image)
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>

<div class="form-group">
    {!! Form::label('Summary (Optional)') !!} {!! add_help('A short blurb that shows up on the weather page and widget. HTML cannot be used here.') !!}
    {!! Form::text('summary', $weather->summary, ['class' => 'form-control', 'maxLength' => 250]) !!}
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!} {!! add_help('This is a full description of the weather that shows up on the full weather page.') !!}
    {!! Form::textarea('description', $weather->description, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('is_visible', 1, $weather->id ? $weather->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_visible', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Weathers that are not active will be hidden from the weather list. They can still be rolled on in a season table.') !!}
</div>

<div class="text-right">
    {!! Form::submit($weather->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@if($weather->id)
    <h3>Preview</h3>
    <div class="card mb-3">
        <div class="card-body">
            @include('world._weather_entry', ['imageUrl' => $weather->weatherImageUrl, 'name' => $weather->displayName, 'description' => $weather->parsed_description])
        </div>
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {    
    $('.delete-weather-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/weather/weathers/delete') }}/{{ $weather->id }}", 'Delete Weather');
    });
});
    
</script>
@endsection