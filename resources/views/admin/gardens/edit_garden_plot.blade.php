@extends('admin.layout')

@section('admin-title')
    Edit Garden Plot
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin', 'Gardens' => 'admin/gardens', 'Edit Garden' => 'admin/gardens/edit/' . $garden->id,
        'Edit Plot' => 'admin/gardens/edit/' . $plot->id . '/plots/edit/' . $plot->id
    ]) !!}

    <h1>
        Edit Garden Plot
        @if ($plot->id)
            <a href="#" class="btn btn-outline-danger float-right delete-plot-button">Delete Garden Plot</a>
        @endif
    </h1>

    {{-- {!! Form::open(['url' => 'admin/gardens/edit/' . $garden->id . '/plots/edit/' . $plot->id, 'files' => true]) !!} --}}

    @include('widgets._add_limits', ['object' => $plot])



@endsection
@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-plot-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/gardens/edit/'.$garden->id.'/plots/delete') }}/{{ $plot->id }}", 'Delete Garden Plot');
            });
        });
    </script>
@endsection