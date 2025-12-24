@extends('admin.layout')

@section('admin-title')
    {{ $object->id ? 'Edit' : 'Create' }} Garden Object
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Gardens' => 'admin/gardens',
        'Garden Objects' => 'admin/gardens/objects',
        ($object->id ? 'Edit' : 'Create') . ' Garden Object' => $object->id ? 'admin/gardens/objects/edit/' . $object->id : 'admin/gardens/objects/create',
    ]) !!}

    <h1>{{ $object->id ? 'Edit' : 'Create' }} Garden Object
        @if ($object->id)
            <a href="#" class="btn btn-outline-danger float-right delete-garden-button">Delete Garden Object</a>
        @endif
    </h1>

    {!! Form::open(['url' => $object->id ? 'admin/gardens/objects/edit/' . $object->id : 'admin/gardens/objects/create', 'files' => true]) !!}

    <div class="card mb-3">
        <div class="card-header h3">Basic Information</div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md">
                    {!! Form::label('type', 'Type') !!}
                    {!! Form::select('type', $object_types, $object->type, ['class' => 'form-control type', 'placeholder' => 'Select Type']) !!}
                </div>
                <div class="form-group col-md">
                    {!! Form::label('object_model', 'Object Type') !!}
                    {!! Form::select('object_model', $object_models, $object->object_model, ['class' => 'form-control object-model', 'placeholder' => 'Select Object Type']) !!}
                </div>
                <div class="form-group col-md select-object">
                    {!! Form::label('object_id', 'Select Object') !!}
                    {!! Form::select('object_id', $objects, $object->object_id, ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>
    </div>

    <div id="type-options">
        @if ($object->id)
            @include('admin.gardens.objects.types.' . $object->type, ['object' => $object])
        @endif
    </div>

    <div class="text-right">
        {!! Form::submit($object->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._loot_select_row', ['showLootTables' => true, 'showRaffles' => true])
    @include('widgets._loot_select_row', [
        'showLootTables' => true,
        'showRaffles' => true,
        'prefix' => 'potential_',
        'extra_fields' => [
            'chance' => [
                'type' => 'number',
                'label' => 'Chance (%)',
                'attributes' => ['step' => '0.01', 'min' => '0', 'max' => '100'],
                'default' => 100,
            ],
        ],
    ])
@endsection
@section('scripts')
    @parent
    <script>
        $(document).ready(function() {

            $('.delete-garden-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/gardens/objects/delete') }}/{{ $object->id }}", 'Delete Garden');
            });

            $('.object-model').on('change', function() {
                let object_model = $(this).val();
                $.get("{{ url('admin/gardens/objects/get-objects') }}/" + encodeURI(object_model), function(data) {
                    $('.select-object').html(data);
                });
            });

            $('.type').on('change', function() {
                let type = $(this).val();
                $.get("{{ url('admin/gardens/objects/type-options') }}/" + type, function(data) {
                    $('#type-options').html(data);
                });
            });
        });
    </script>
@endsection
