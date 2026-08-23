{!! Form::open(['url' => $location->id ? 'admin/maps/locations/edit/' . $location->id . '/' . $map->id : 'admin/maps/locations/create/' . $map->id]) !!}

<h3>{{ $location->id ? 'Edit' : 'Create' }} Location</h3>

<div class="form-group">
    {!! Form::label('name', 'Name') !!}
    {!! Form::text('name', $location->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('description', 'Description') !!}
    {!! Form::textarea('description', $location->description, ['class' => 'form-control', 'cols' => 4]) !!}
</div>

<div class="row">
    <div class="col-md">
        <div class="form-group">
            <p>Coordinates should be comma seperated integers.
                <br><b>Example:</b>
                <br>100, 200
                <br>or
                <br>254,334,278,390
            </p>
            {!! Form::label('cords', 'Coordinates') !!}
            {!! Form::text('cords', $location->cords, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('shape', 'Shape') !!}
            {!! Form::select('shape', ['rect' => 'Rect', 'poly' => 'Poly', 'circle' => 'Circle'], $location->shape, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>



<div class="row">
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('link', 'Link (Optional)') !!}
            {!! Form::text('link', $location->link, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('link_type', 'Link Type') !!}
            {!! Form::select('link_type', ['GET' => 'GET', 'POST' => 'POST'], $location->link_type, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="col-md form-group">
    {!! Form::checkbox('is_active', 1, $location->id ? $location->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!}
</div>

<div class="text-right">
    {!! Form::submit($location->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}
