@extends('admin.layout')

@section('admin-title')
    {{ ucfirst(__('dailies.daily')) }}
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', ucfirst(__('dailies.daily')) => 'admin/data/dailies']) !!}

    <h1>{{ ucfirst(__('dailies.daily')) }}</h1>

    <p>This is a list of {{ __('dailies.dailies') }} that users can roll each day.</p>
    <p>The sorting order reflects the order in which the {{ __('dailies.daily') }} will be listed on the {{ __('dailies.daily') }} index.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/dailies/create') }}"><i class="fas fa-plus"></i> Create New {{ __('dailies.daily') }}</a></div>
    @if (!count($dailies))
        <p>No {{ __('dailies.dailies') }} found.</p>
    @else
        <table class="table table-sm daily-table">
            <tbody id="sortable" class="sortable">
                @foreach ($dailies as $daily)
                    <tr class="sort-item" data-id="{{ $daily->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            {!! $daily->displayName !!}
                            @if ($daily->is_timed_daily)
                                <i class="fas fa-clock"></i>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/dailies/edit/' . $daily->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/dailies/sort']) !!}
            {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#sortable").sortable({
                items: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
