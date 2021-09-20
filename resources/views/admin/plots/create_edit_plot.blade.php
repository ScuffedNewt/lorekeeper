@extends('admin.layout')

@section('admin-title') Plots @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Plots' => 'admin/data/plots', ($plot->id ? 'Edit' : 'Create').' Plot' => $plot->id ? 'admin/data/plots/edit/'.$plot->id : 'admin/data/plots/create']) !!}

<h1>{{ $plot->id ? 'Edit' : 'Create' }} Plot
    @if($plot->id)
        <a href="#" class="btn btn-outline-danger float-right delete-plot-button">Delete Plot</a>
    @endif
</h1>

{!! Form::open(['url' => $plot->id ? 'admin/data/plots/edit/'.$plot->id : 'admin/data/plots/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="row">
    <div class="col-md form-group">
        {!! Form::checkbox('free', 1, $plot->id ? $plot->free : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('free', 'Should this plot be free?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will need to pay to unlock the plot.') !!}
    </div>
</div>

<div class="form-group">
    <p> Leave empty if the plot is going to be free </p>
    {!! Form::label('plot_cost') !!}
    {!! Form::number('plot_cost', $plot->plot_cost, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    <p> Leave empty if the plot is going to be free </p>
    {!! Form::label('currency_id') !!}
    {!! Form::select('currency_id', $currencies, null, ['class' => 'form-control']) !!}
</div>
<hr>
<div class="form-group">
    {!! Form::label('plot_type', 'Plot Type') !!} {!!add_help('Determines what produce the plot can hold.') !!}
    {!! Form::select('plot_type', ['Seed' => 'Seed', 'Coop' => 'Coop', 'Barn' => 'Barn'], $plot->plot_type, ['class' => 'form-control']) !!}
</div>

<div class="text-right">
    {!! Form::submit($plot->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.selectize').selectize();

    $('.delete-plot-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/plots/delete') }}/{{ $plot->id }}", 'Delete Plot');
    });
});

</script>
@endsection
