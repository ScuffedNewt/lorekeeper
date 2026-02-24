@extends('admin.layout')

@section('admin-title')
    Stats
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Stats' => 'admin/stats', ($stat->id ? 'Edit' : 'Create') . ' Stat' => $stat->id ? 'admin/stats/edit/' . $stat->id : 'admin/stats/create']) !!}

    <h1>{{ $stat->id ? 'Edit' : 'Create' }} Stat
        @if ($stat->id)
            <a href="#" class="btn btn-outline-danger float-right delete-stat-button">Delete Stat</a>
        @endif
    </h1>

    {!! Form::open(['url' => $stat->id ? 'admin/stats/edit/' . $stat->id : 'admin/stats/create']) !!}

    <h3>Basic Information</h3>

    @if (!config('lorekeeper.claymores_and_companions.stat_points.general_id'))
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i>
            No general stat point is set in the Claymores and Companions config. This means users cannot obtain stat points to use on characters. Please set a general stat point in the config to enable this functionality.
            <br />
            Once a general stat point is set, the edit options will be hidden aside from the name, abbreviation and icon, as other fields will not be used.

            @if (!$stat->id)
                <div class="alert alert-info mt-2 mb-0">
                    <i class="fas fa-info-circle"></i>
                    Because there is no general stat point set, you will only be able to see the other fields after stat creation in the event that you want to use this stat as the general stat point.
                </div>
            @endif
        </div>
    @endif

    @if ($stat->id && config('lorekeeper.claymores_and_companions.stat_points.general_id') != $stat->id)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Be careful when editing stats, as changes to the base, increment or multiplier can have unintended consequences on characters that already have levels in the stat.
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            This stat is currently set as the general stat point.
        </div>
    @endif

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $stat->name, ['class' => 'form-control', 'placeholder' => 'E.g Health']) !!}
            </div>
        </div>
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Abbreviation (Optional)') !!}
                {!! Form::text('abbreviation', $stat->abbreviation, ['class' => 'form-control', 'placeholder' => 'E.g HP']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md form-group">
            <div class="row">
                <div class="col-8">
                    {!! Form::label('Stat Icon (Font-awesome code; optional)') !!}
                </div>
                <div class="col-4">
                    <i id="siconitem" class="{{ $stat->icon }}"></i>
                </div>
            </div>
            {!! Form::text('icon', $stat->icon, ['class' => 'form-control', 'id' => 'icon']) !!}
        </div>
        <div class="col-md form-group">
            {!! Form::label('Colour') !!} {!! add_help('This is the colour that will be used to display the stat on the character page. Set it to white to disable.') !!}
            {!! Form::color('colour', $stat->colour, ['class' => 'form-control']) !!}
        </div>
    </div>

    @if ($stat->id != config('lorekeeper.claymores_and_companions.stat_points.general_id'))
        <div class="form-group">
            {!! Form::label('Base Stat') !!} {!! add_help('This is the \'default\' or \'starter\' amount of stat. Can be negative. If negative, all level ups will apply as if the base was 1.') !!}
            {!! Form::number('base', $stat->base, ['class' => 'form-control']) !!}
        </div>

        <h3>Level Up Information</h3>
        <p>
            Multiplier can apply to the increment (e.g (current stat value + increment) * Multiplier) or just to current stat value. Leave the increment blank if you want it to apply just to current stat value.
        </p>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            If a stat calculation is a decimal it will be rounded to the nearest whole number.
        </div>
        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('Increment (Optional)') !!} {!! add_help('If you want a stat to increase more than by 1 per level up, enter a unique increment here.') !!}
                    {!! Form::text('increment', $stat->increment, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('Multiplier (Optional)') !!} {!! add_help('If you want the stat to increase based on a multiplication set it here.') !!}
                    {!! Form::text('multiplier', $stat->multiplier, ['class' => 'form-control', 'placeholder' => 'E.g. 1.1 = 10% increase']) !!}
                </div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('Max level (Optional)') !!} {!! add_help('A max level can be applied here if you want to cap the level a character can gain in this stat.') !!}
            {!! Form::text('max_level', $stat->max_level, ['class' => 'form-control']) !!}
        </div>

        @if ($stat->id)
            <hr />
            <h3>Custom Species / Subtypes Bases</h3>
            <p>If you want this stat to have different bases for different species / subtypes, select them below.</p>
            <div class="form-group">
                {!! Form::label('Species / Subtypes') !!}
                <div id="statList">
                    @if (isset($stat->data['bases']) && $stat->data['bases'])
                        @foreach ($stat->data['bases'] as $type => $bases)
                            @foreach ($bases as $id => $value)
                                <div class="row mb-2">
                                    <div class="col-md-3">
                                        {!! Form::select('base_types[]', ['species' => 'Species', 'subtype' => 'Subtype'], $type, ['class' => 'form-control mr-2 type', 'placeholder' => 'Select Type']) !!}
                                    </div>
                                    <div class="col-md-4 typeid">
                                        {!! Form::select('base_type_ids[]', $type == 'species' ? $specieses : $subtypes, $id, ['class' => 'form-control mr-2 stat-select stat-species', 'placeholder' => 'Select Species']) !!}
                                    </div>
                                    <div class="col-md-4 base">
                                        {!! Form::number('base_values[]', $value, ['class' => 'form-control', 'placeholder' => 'Stat Base for Species / Subtype']) !!}
                                    </div>
                                    <a href="#" class="remove-stat btn btn-danger mb-2 ml-auto mr-3">×</a>
                                </div>
                            @endforeach
                        @endforeach
                    @endif
                </div>
                <div><a href="#" class="btn btn-primary" id="add-stat">Add Species</a></div>
            </div>
            <hr />
            <h3>Species / Subtypes Restrictions</h3>
            <p>If you want this stat to only apply to certain species / subtypes, select them below.</p>
            <p>If you select a species, all subtypes of that species will be included.</p>
            <div class="form-group">
                {!! Form::label('Species / Subtypes') !!} {!! add_help('Allow only the selected species / subtypes to have this stat.') !!}
                <div id="limitList">
                    @foreach ($stat->limits as $limit)
                        <div class="row mb-2">
                            <div class="col-md-5">
                                {!! Form::select('types[]', ['species' => 'Species', 'subtype' => 'Subtype'], !$limit->is_subtype ? 'species' : 'subtype', ['class' => 'form-control mr-2 type', 'placeholder' => 'Select Type']) !!}
                            </div>
                            <div class="col-md-6 typeid">
                                {!! Form::select('type_ids[]', !$limit->is_subtype ? $specieses : $subtypes, $limit->species_id, ['class' => 'form-control mr-2 limit-select species', 'placeholder' => 'Select Species']) !!}
                            </div>
                            <a href="#" class="remove-limit btn btn-danger mb-2 ml-auto mr-3">×</a>
                        </div>
                    @endforeach
                </div>
                <div><a href="#" class="btn btn-primary" id="add-limit">Add Species</a></div>
            </div>
        @endif
    @endif

    <div class="text-right">
        {!! Form::submit($stat->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($stat->id)
        {{-- Bases --}}
        <div class="row stat-row hide mb-2">
            <div class="col-md-3">
                {!! Form::select('base_types[]', ['species' => 'Species', 'subtype' => 'Subtype'], null, ['class' => 'form-control mr-2 stat-type', 'placeholder' => 'Select Type']) !!}
            </div>
            <div class="col-md-4 statid">
            </div>
            <div class="col-md-4 base">
                {!! Form::number('base_values[]', null, ['class' => 'form-control', 'placeholder' => 'Stat Base for Species / Subtype']) !!}
            </div>
            <a href="#" class="remove-stat btn btn-danger mb-2 ml-auto mr-3">×</a>
        </div>

        <div class="hide">
            <div class="original stat-species">
                {!! Form::select('base_type_ids[]', $specieses, null, ['class' => 'form-control mr-2 stat-select species', 'placeholder' => 'Select Species']) !!}
            </div>
            <div class="original stat-subtype">
                {!! Form::select('base_type_ids[]', $subtypes, null, ['class' => 'form-control mr-2 stat-select subtype', 'placeholder' => 'Select Subtype']) !!}
            </div>
        </div>

        {{-- Limits --}}
        <div class="row limit-row hide mb-2">
            <div class="col-md-5">
                {!! Form::select('types[]', ['species' => 'Species', 'subtype' => 'Subtype'], null, ['class' => 'form-control mr-2 type', 'placeholder' => 'Select Type']) !!}
            </div>
            <div class="col-md-6 typeid">
            </div>
            <a href="#" class="remove-limit btn btn-danger mb-2 ml-auto mr-3">×</a>
        </div>

        <div class="hide">
            <div class="original species">
                {!! Form::select('type_ids[]', $specieses, null, ['class' => 'form-control mr-2 limit-select species', 'placeholder' => 'Select Species']) !!}
            </div>
            <div class="original subtype">
                {!! Form::select('type_ids[]', $subtypes, null, ['class' => 'form-control mr-2 limit-select subtype', 'placeholder' => 'Select Subtype']) !!}
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.original.stat-select').selectize();
            $('#add-stat').on('click', function(e) {
                e.preventDefault();
                addStatRow();
            });
            $('.remove-stat').on('click', function(e) {
                e.preventDefault();
                removeStatRow($(this));
            })

            function addStatRow() {
                var $clone = $('.stat-row').clone();
                $('#statList').append($clone);
                $clone.removeClass('hide stat-row');
                $clone.addClass('d-flex');
                $clone.find('.remove-stat').on('click', function(e) {
                    e.preventDefault();
                    removeStatRow($(this));
                })
                $clone.find('.stat-select').selectize();
                attachStatTypeChangeListener($clone.find('.stat-type'));
            }

            function removeStatRow($trigger) {
                $trigger.parent().remove();
            }

            function attachStatTypeChangeListener(node) {
                node.on('change', function(e) {
                    e.preventDefault();
                    var val = $(this).val();
                    var $cell = $(this).parent().parent().find('.statid');
                    var $clone = null;
                    if (val == 'species') {
                        $clone = $('.original.stat-species').clone();
                    } else if (val == 'subtype') {
                        $clone = $('.original.stat-subtype').clone();
                    }
                    $cell.html($clone);
                    $clone.removeClass('hide original');
                });
            }


            // LIMITS
            $('.original.limit-select').selectize();
            $('#add-limit').on('click', function(e) {
                e.preventDefault();
                addLimitRow();
            });
            $('.remove-limit').on('click', function(e) {
                e.preventDefault();
                removeLimitRow($(this));
            })

            function addLimitRow() {
                var $clone = $('.limit-row').clone();
                $('#limitList').append($clone);
                $clone.removeClass('hide limit-row');
                $clone.addClass('d-flex');
                $clone.find('.remove-limit').on('click', function(e) {
                    e.preventDefault();
                    removeLimitRow($(this));
                })
                $clone.find('.limit-select').selectize();
                attachLimitTypeChangeListener($clone.find('.type'));
            }

            function removeLimitRow($trigger) {
                $trigger.parent().remove();
            }

            function attachLimitTypeChangeListener(node) {
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

            $('.delete-stat-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/stats/delete') }}/{{ $stat->id }}", 'Delete Stat');
            });

            $("#icon").change(function() {
                var text = $('#icon').val();
                $("#siconitem").removeClass();
                $("#siconitem").addClass(text);
            });
        });
    </script>
@endsection
