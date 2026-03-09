@extends('admin.layout')

@section('admin-title')
    Skills
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Skills' => 'admin/data/skills', ($skill->id ? 'Edit' : 'Create') . ' Skill' => $skill->id ? 'admin/data/skills/edit/' . $skill->id : 'admin/data/skills/create']) !!}

    <h1>{{ $skill->id ? 'Edit' : 'Create' }} Skill
        @if ($skill->id)
            <a href="#" class="btn btn-outline-danger float-right delete-skill-button">Delete Skill</a>
        @endif
    </h1>

    {!! Form::open(['url' => $skill->id ? 'admin/data/skills/edit/' . $skill->id : 'admin/data/skills/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('Name') !!}
            {!! Form::text('name', $skill->name, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::label('Skill Category (Optional)') !!}
            {!! Form::select('skill_category_id', $categories, $skill->skill_category_id, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($skill->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $skill->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Parent (Optional)') !!} {!! add_help('Related skill that transforms into this skill.') !!}
                <p class="mb-0">A parent locks this skill and all prompts associated with this skill until the parent level is reached. It is also in the same tree as the skill.</p>
                {!! Form::select('parent_id', $skills, $skill->parent_id, ['class' => 'form-control mb-1']) !!}
            </div>
        </div>
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Parent Level (Optional)') !!} {!! add_help('Related skill that transforms into this skill.') !!}
                {!! Form::number('parent_level', $skill->parent_level ? $skill->parent_level : 1, ['class' => 'form-control', 'min' => 1]) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Prerequisite (Optional)') !!} {!! add_help('Unrelated skill required to have before the character can learn this skill.') !!}
        <p class="mb-0">A prerequisite is required to have at least level 1 in to enter any prompts with this skill reward.</p>
        {!! Form::select('prerequisite_id', $skills, $skill->prerequisite_id, ['class' => 'form-control mb-1']) !!}
    </div>


    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $skill->id ? $skill->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, this class will not be visible on world pages.') !!}
    </div>

    <hr />

    @if ($skill->id)
        <div class="form-group">
            {!! Form::label('Species / Subtypes') !!} {!! add_help('Allow only the selected species / subtypes to have this skill.') !!}
            <div id="featureList">
                @foreach ($skill->species as $species)
                    <div class="d-flex mb-2">
                        <div class="col-md-5">
                            {!! Form::select('types[]', ['species' => 'Species', 'subtype' => 'Subtype'], !$species->is_subtype ? 'species' : 'subtype', ['class' => 'form-control mr-2 type', 'placeholder' => 'Select Type']) !!}
                        </div>
                        <div class="col-md-6 typeid">
                            @if ($species->type == 'species')
                                {!! Form::select('type_ids[]', !$species->is_subtype ? $specieses : $subtypes, $species->species_id, ['class' => 'form-control mr-2 feature-select species', 'placeholder' => 'Select Species']) !!}
                            @else
                                {!! Form::select('type_ids[]', !$species->is_subtype ? $specieses : $subtypes, $species->species_id, ['class' => 'form-control mr-2 feature-select subtype', 'placeholder' => 'Select Subtype']) !!}
                            @endif
                        </div>
                        <a href="#" class="remove-feature btn btn-danger mb-2">×</a>
                    </div>
                @endforeach
            </div>
            <div><a href="#" class="btn btn-primary" id="add-feature">Add Species</a></div>
        </div>
    @endif

    <div class="text-right">
        {!! Form::submit($skill->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($skill->id)
        <div class="feature-row hide mb-2">
            <div class="col-md-5">
                {!! Form::select('types[]', ['species' => 'Species', 'subtype' => 'Subtype'], null, ['class' => 'form-control mr-2 type', 'placeholder' => 'Select Type']) !!}
            </div>
            <div class="col-md-6 typeid">
            </div>
            <a href="#" class="remove-feature btn btn-danger mb-2">×</a>
        </div>

        <div class="hide">
            <div class="original species">
                {!! Form::select('type_ids[]', $specieses, null, ['class' => 'form-control mr-2 feature-select species', 'placeholder' => 'Select Species']) !!}
            </div>
            <div class="original subtype">
                {!! Form::select('type_ids[]', $subtypes, null, ['class' => 'form-control mr-2 feature-select subtype', 'placeholder' => 'Select Subtype']) !!}
            </div>
        </div>
    @endif

    @if ($skill->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._skill_entry', ['imageUrl' => $skill->imageUrl, 'name' => $skill->displayName, 'description' => $skill->description, 'searchUrl' => $skill->searchUrl, 'visible' => $skill->is_visible])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();

            $('.original.feature-select').selectize();
            $('#add-feature').on('click', function(e) {
                e.preventDefault();
                addFeatureRow();
            });
            $('.remove-feature').on('click', function(e) {
                e.preventDefault();
                removeFeatureRow($(this));
            })

            function addFeatureRow() {
                var $clone = $('.feature-row').clone();
                $('#featureList').append($clone);
                $clone.removeClass('hide feature-row');
                $clone.addClass('d-flex');
                $clone.find('.remove-feature').on('click', function(e) {
                    e.preventDefault();
                    removeFeatureRow($(this));
                })
                $clone.find('.feature-select').selectize();
                attachTypeChangeListener($clone.find('.type'));
            }

            function removeFeatureRow($trigger) {
                $trigger.parent().remove();
            }

            function attachTypeChangeListener(node) {
                node.on('change', function(e) {
                    e.preventDefault();
                    var val = $(this).val();
                    var $cell = $(this).parent().parent().find('.typeid');
                    var $clone = null;
                    if (val == 'species') {
                        $clone = $('.original.species').clone();
                    } else if (val == 'subtype') {
                        $clone = $('.original.subtype').clone();
                    }
                    $cell.html($clone);
                    $clone.removeClass('hide original');
                });
            }

            $('.delete-skill-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/skills/delete') }}/{{ $skill->id }}", 'Delete Skill');
            });
        });
    </script>
@endsection
