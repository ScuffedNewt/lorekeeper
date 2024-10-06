@extends('admin.layout')

@section('admin-title')
    Edit Garden Plot
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Gardens' => 'admin/gardens',
        'Edit Garden' => 'admin/gardens/edit/' . $garden->id,
        'Edit Plot' => 'admin/gardens/edit/' . $plot->id . '/plots/edit/' . $plot->id,
    ]) !!}

    <h1>
        Edit Garden Plot
        @if ($plot->id)
            <a href="#" class="btn btn-outline-danger float-right delete-plot-button">Delete Garden Plot</a>
        @endif
    </h1>

    {{-- {!! Form::open(['url' => 'admin/gardens/edit/' . $garden->id . '/plots/edit/' . $plot->id, 'files' => true]) !!} --}}

    <div class="alert alert-info">
        <b>Note</b>: plot unlocks are handled differently than other limits. The limits are not automatically unlocked.
    </div>
    @include('widgets._add_limits', [
        'object' => $plot,
        'extra_options' => false,
        'extra_options_values' => [
            'is_unlocked' => 'yes',
            'is_auto_unlocked' => 'no',
        ],
    ])
@endsection
@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-plot-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/gardens/edit/' . $garden->id . '/plots/delete') }}/{{ $plot->id }}", 'Delete Garden Plot');
            });
        });
    </script>
@endsection
