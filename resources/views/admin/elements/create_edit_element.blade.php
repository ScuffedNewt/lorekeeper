@extends('admin.layout')

@section('admin-title')
    Elements
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Elements' => 'admin/data/elements', ($element->id ? 'Edit' : 'Create') . ' Element' => $element->id ? 'admin/data/elements/edit/' . $element->id : 'admin/data/elements/create']) !!}

    <h1>{{ $element->id ? 'Edit' : 'Create' }} Element
        @if ($element->id)
            <a href="#" class="btn btn-outline-danger float-right delete-element-button">Delete Element</a>
        @endif
    </h1>

    {!! Form::open(['url' => $element->id ? 'admin/data/elements/edit/' . $element->id : 'admin/data/elements/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $element->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Colour (Hex code; optional)') !!} {!! add_help('This will be the colour of the tag / badge that appears on objects with this element typing') !!}
        <div class="input-group cp">
            {!! Form::text('colour', $element->colour, ['class' => 'form-control']) !!}
            <span class="input-group-append">
                <span class="input-group-text colorpicker-input-addon"><i></i></span>
            </span>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($element->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $element->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    @if ($element->id)
        <h4>Weaknesses</h4>
        <div class="text-right"><a href="#" class="btn btn-primary mb-2" id="add-weakness">Add Weakness</a></div>
        <p>Set any weaknesses this element has.</p>
        <p>Resistances are automatically calculated based on the weaknesses of other elements.</p>
        <div id="weaknesses">
            @foreach ($element->weaknesses as $weakness)
                <div class="d-flex mb-2">
                    {!! Form::select('weakness_id[]', $elements, $weakness->weakness_id, ['class' => 'form-control selectize mx-1', 'placeholder' => 'Select Element']) !!}
                    {!! Form::number('weakness_multiplier[]', $weakness->multiplier, ['class' => 'form-control mx-1', 'step' => 0.1, 'max' => 2, 'min' => 1.1, 'placeholder' => 'Set Multiplier']) !!}
                    <div class="btn btn-danger remove-weakness ml-2">Remove</div>
                </div>
            @endforeach
        </div>

        <h4>Immunites</h4>
        <div class="text-right"><a href="#" class="btn btn-primary mb-2" id="add-immunity">Add Immunity</a></div>
        <p>Set any immunites this element has.</p>
        <div id="immunities">
            @foreach ($element->immunities as $immunity)
                <div class="d-flex mb-2">
                    {!! Form::select('immunity_id[]', $elements, $immunity->immunity_id, ['class' => 'form-control selectize mx-1', 'placeholder' => 'Select Element']) !!}
                    <div class="btn btn-danger remove-immunity ml-2">Remove</div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="text-right">
        {!! Form::submit($element->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <div class="weakness-row hide mb-2">
        {!! Form::select('weakness_id[]', $elements, null, ['class' => 'form-control weakness-select mx-1', 'placeholder' => 'Select Element']) !!}
        {!! Form::number('weakness_multiplier[]', null, ['class' => 'form-control mx-1', 'step' => 0.1, 'max' => 2, 'min' => 1.1, 'placeholder' => 'Set Multiplier']) !!}
        <div class="btn btn-danger remove-weakness ml-2">Remove</div>
    </div>

    <div class="immunity-row hide mb-2">
        {!! Form::select('immunity_id[]', $elements, null, ['class' => 'form-control immunity-select mx-1', 'placeholder' => 'Select Element']) !!}
        <div class="btn btn-danger remove-immunity ml-2">Remove</div>
    </div>

    <hr />

    @if ($element->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._element_entry', [
                    'imageUrl' => $element->imageUrl,
                    'name' => $element->displayName,
                    'description' => $element->parsed_description,
                    'idUrl' => $element->idUrl,
                    'searchUrl' => $element->searchUrl,
                ])
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();

            $('.delete-element-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/elements/delete') }}/{{ $element->id }}", 'Delete Element');
            });

            $('#add-weakness').on('click', function(e) {
                e.preventDefault();
                addWeaknessRow();
            });

            // apply to existing weakness rows
            $('.remove-weakness').on('click', function(e) {
                e.preventDefault();
                removeFeatureRow($(this));
            });

            function addWeaknessRow() {
                var $clone = $('.weakness-row').clone();
                $('#weaknesses').append($clone);
                $clone.removeClass('hide feature-row');
                $clone.addClass('d-flex');
                $clone.find('.remove-weakness').on('click', function(e) {
                    e.preventDefault();
                    removeFeatureRow($(this));
                });
                $clone.find('.weakness-select').selectize();
            }

            function removeFeatureRow($trigger) {
                $trigger.parent().remove();
            }

            $('#add-immunity').on('click', function(e) {
                e.preventDefault();
                addImmunityRow();
            });

            // apply to existing weakness rows
            $('.remove-immunity').on('click', function(e) {
                e.preventDefault();
                removeFeatureRow($(this));
            });

            function addImmunityRow() {
                var $clone = $('.immunity-row').clone();
                $('#immunities').append($clone);
                $clone.removeClass('hide feature-row');
                $clone.addClass('d-flex');
                $clone.find('.remove-immunity').on('click', function(e) {
                    e.preventDefault();
                    removeFeatureRow($(this));
                });
                $clone.find('.immunity-select').selectize();
            }
        });
    </script>
@endsection
