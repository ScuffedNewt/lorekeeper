@extends('admin.layout')

@section('admin-title')
    {{ $feature->id ? 'Edit' : 'Create' }} Trait
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Traits' => 'admin/data/traits', ($feature->id ? 'Edit' : 'Create') . ' Trait' => $feature->id ? 'admin/data/traits/edit/' . $feature->id : 'admin/data/traits/create']) !!}

    <h1>{{ $feature->id ? 'Edit' : 'Create' }} Trait
        @if ($feature->id)
            <a href="#" class="btn btn-danger float-right delete-feature-button">Delete Trait</a>
        @endif
    </h1>

    {!! Form::open(['url' => $feature->id ? 'admin/data/traits/edit/' . $feature->id : 'admin/data/traits/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('Name') !!}
            {!! Form::text('name', $feature->name, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::label('Rarity') !!}
            {!! Form::select('rarity_id', $rarities, $feature->rarity_id, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($feature->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            {!! Form::label('Trait Category (Optional)') !!}
            {!! Form::select('feature_category_id', $categories, $feature->feature_category_id, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-4 form-group">
            {!! Form::label('Species Restriction (Optional)') !!}
            {!! Form::select('species_id', $specieses, $feature->species_id, ['class' => 'form-control', 'id' => 'species']) !!}
        </div>
        <div class="col-md-4 form-group" id="subtypes">
            {!! Form::label('Subtype (Optional)') !!} {!! add_help('This is cosmetic and does not limit choice of traits in selections.') !!}
            {!! Form::select('subtype_id', $subtypes, $feature->subtype_id, ['class' => 'form-control', 'id' => 'subtype']) !!}
        </div>
    </div>

    @if ($feature->id)
        <hr />
        <h4>Alternative Rarities</h4>
        <p>If you want a trait to have different rarities depending on species / subtype set them here.</p>
        <div class="text-right">
            <div class="btn btn-primary" id="addAlternativeRarity">Add Alternative Rarity</div>
        </div>
        <div id="alternativeRarities">
            @foreach ($feature->alternative_rarities ?? [] as $species_id=>$valuesArray)
                @foreach ($valuesArray as $values)
                    <div class="row">
                        <div class="col-md-4 form-group">
                            {!! Form::label('Species') !!}
                            {!! Form::select('alternative_rarities[species_id][]', $specieses, $species_id, ['class' => 'form-control selectize species']) !!}
                        </div>
                        <div class="col-md-4 form-group subtype">
                            {!! Form::label('Subtype (Optional)') !!}
                            {!! Form::select('alternative_rarities[subtype_id][]', $subtypes, (!isset($values['subtype_id']) || !$values['subtype_id']) ? 'none' : $values['subtype_id'], ['class' => 'form-control', 'placeholder' => 'Select Subtype']) !!}
                        </div>
                        <div class="col-md-4 form-group">
                            {!! Form::label('Rarity') !!}
                            {!! Form::select('alternative_rarities[rarity_id][]', $rarities, $values['rarity_id'], ['class' => 'form-control selectize']) !!}
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
        <hr />
    @endif

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $feature->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $feature->id ? $feature->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the trait will not be visible in the trait list or available for selection in search and design updates. Permissioned staff will still be able to add them to characters, however.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($feature->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($feature->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._feature_entry', ['feature' => $feature])
            </div>
        </div>
    @endif

    <div class="hide alt-rarity-row">
        <div class="row">
            <div class="col-md-4 form-group">
                {!! Form::label('Species') !!}
                {!! Form::select('alternative_rarities[species_id][]', $specieses, null, ['class' => 'form-control selectize species']) !!}
            </div>
            <div class="col-md-4 form-group subtype">
                {!! Form::label('Subtype (Optional)') !!}
                {!! Form::select('alternative_rarities[subtype_id][]', [], null, ['class' => 'form-control', 'placeholder' => 'Select Subtype']) !!}
            </div>
            <div class="col-md-4 form-group">
                {!! Form::label('Rarity') !!}
                {!! Form::select('alternative_rarities[rarity_id][]', $rarities, null, ['class' => 'form-control selectize']) !!}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-feature-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/traits/delete') }}/{{ $feature->id }}", 'Delete Trait');
            });
            refreshSubtype();

            $('#addAlternativeRarity').on('click', function() {
                var row = $('.alt-rarity-row').clone();
                row.removeClass('hide').removeClass('alt-rarity-row');
                row.find('.selectize').selectize();
                row.find('.species').on('change', function() {
                    changeSubtype(this);
                });
                $('#alternativeRarities').append(row);
            });
        });

        $("#species").change(function() {
            refreshSubtype();
        });

        function changeSubtype(node) {
            var species = node.value;
            var row = $(node).closest('.row');
            var subtype = row.find('.subtype');
            $.ajax({
                type: "GET",
                url: "{{ url('admin/data/traits/check-subtype') }}?species=" + species,
                dataType: "text"
            }).done(function(res) {
                subtype.html(res);
                subtype.find('select').attr('name', 'alternative_rarities[subtype_id][]');
                $('[data-toggle="tooltip"]').tooltip({
                    html: true
                });
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        }

        function refreshSubtype() {
            var species = $('#species').val();
            var subtype_id = {{ $feature->subtype_id ?: 'null' }};
            $.ajax({
                type: "GET",
                url: "{{ url('admin/data/traits/check-subtype') }}?species=" + species + "&subtype_id=" + subtype_id,
                dataType: "text"
            }).done(function(res) {
                $("#subtypes").html(res);
                $('[data-toggle="tooltip"]').tooltip({
                    html: true
                });
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        };
    </script>
@endsection
