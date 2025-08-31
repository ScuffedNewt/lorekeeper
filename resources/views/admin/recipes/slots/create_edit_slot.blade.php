@extends('admin.layout')

@section('admin-title')
    Crafting Recipe Slots
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Slots' => 'admin/data/recipes/slots', ($slot->id ? 'Edit' : 'Create') . ' Slot' => $slot->id ? 'admin/data/recipes/slots/edit/' . $slot->id : 'admin/data/recipes/slots/create']) !!}

    <h1>{{ $slot->id ? 'Edit' : 'Create' }} Crafting Recipe Slot
        @if ($slot->id)
            <a href="#" class="btn btn-outline-danger float-right delete-slot-button">Delete Slot</a>
        @endif
    </h1>

    {!! Form::open(['url' => $slot->id ? 'admin/data/recipes/slots/edit/' . $slot->id : 'admin/data/recipes/slots/create', 'files' => true]) !!}

    <div class="form-group">
        {!! Form::label('name', 'Slot Name') !!}
        {!! Form::text('name', $slot->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $slot->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit($slot->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($slot->id)
        @include('widgets._add_limits', [
            'object' => $slot,
            'hideAutoUnlock' => true
        ])
    @endif
@endsection

@section('scripts')
    @include('js._tinymce_wysiwyg', ['tinymceSelector' => '.wysiwyg', 'tinymceHeight' => 300])
    @include('widgets._datetimepicker_js')
    @parent
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();

            $('.delete-slot-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/recipes/slots/delete') }}/{{ $slot->id }}", 'Delete Slot');
            });
        });
    </script>
@endsection
