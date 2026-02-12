@extends('admin.layout')

@section('admin-title')
    Pets
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Pets' => 'admin/data/pets', ($pet->id ? 'Edit' : 'Create') . ' Pet' => $pet->id ? 'admin/data/pets/edit/' . $pet->id : 'admin/data/pets/create']) !!}

    <h1>
        {{ $pet->id ? 'Edit' : 'Create' }} Pet
        @if ($pet->id)
            <a href="#" class="btn btn-outline-danger float-right delete-pet-button">Delete Pet</a>
            @if ($pet->dropData)
                <a href="{{ url('/admin/data/pets/drops/edit/') . '/' . $pet->id }}" class="btn btn-info float-right mr-2">Edit Drops</a>
            @else
                <a href="{{ url('/admin/data/pets/drops/create') }}" class="btn btn-info float-right mr-2">Create Drops</a>
            @endif
        @endif
    </h1>

    {!! Form::open(['url' => $pet->id ? 'admin/data/pets/edit/' . $pet->id : 'admin/data/pets/create', 'files' => true]) !!}

    @if (!$pet->id)
        <p>You can create variants once the pet is made.
        <p>
    @endif

    <h2>Basic Information</h2>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $pet->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($pet->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group row no-gutters align-items-center">
        {!! Form::label('pet_category_id', 'Pet Category (Optional)', ['class' => 'col-md mb-0']) !!}
        {!! Form::select('pet_category_id', $categories, $pet->pet_category_id, ['class' => 'col-md-9 form-control']) !!}
    </div>

    <div class="form-group row no-gutters align-items-center">
        <div class="col-md col-form-label">
            {!! Form::label('limit', 'Character Hold Limit (Optional)', ['class' => 'mb-0']) !!} {!! add_help('This limit is per pet and holds lower priority than category limits, if set. If there is a category set, it is only applicable if that category can be attached.') !!}
        </div>
        {!! Form::number('limit', $pet->limit, ['class' => 'col-md-9 form-control px-2']) !!}
    </div>

    @if (!$pet->variants->count())
        <div class="form-group row no-gutters align-items-center">
            <div class="col-md col-form-label">
                {!! Form::label('parent_id', 'Variant of Pet (Optional)', ['class' => 'mb-0']) !!} {!! add_help('If this pet is a variant of another pet, select the parent pet here.') !!}
            </div>
            {!! Form::select('parent_id', $pets, $pet->parent_id, ['class' => 'col-md-9 form-control', 'placeholder' => 'Select Main Pet']) !!}
        </div>
    @endif

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $pet->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::checkbox('is_visible', 1, $pet->id ? $pet->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, the pet will not be visible on world pages.') !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::checkbox('allow_transfer', 1, $pet->id ? $pet->allow_transfer : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('allow_transfer', 'Allow User → User Transfer', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to transfer this pet to other users. Non-account-bound pets can be account-bound when granted to users directly.') !!}
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($pet->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($pet->id)
        <hr />
        @if (!$pet->isVariant)
            <div class="card mb-3">
                <div class="card-header h3">Variants</div>
                <div class="card-body">
                    <p>
                        Variants are different colourations, patterns, or other visual differences that a pet can have.
                        <br />They are technically separate pets, but are treated as variants of the same pet.
                        {{ !config('lorekeeper.pets.include_variants') ? 'They do not have a unique encyclopedia entry.' : '' }}
                    </p>
                    @if ($pet->variants->count())
                        @foreach ($pet->variants->chunk(4) as $chunk)
                            <div class="row {{ $loop->last ? '' : 'mb-3' }}">
                                @foreach ($chunk as $variant)
                                    <div class="col">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                @if ($variant->has_image)
                                                    <a href="{{ $variant->imageUrl }}" data-lightbox="entry" data-title="{{ $variant->name }}">
                                                        <img src="{{ $variant->imageUrl }}" class="img-fluid" alt="{{ $variant->name }}" data-toggle="tooltip" data-title="{{ $variant->name }}" style="max-height:200px" />
                                                    </a>
                                                @else
                                                    {{ $variant->name }}
                                                @endif
                                                <div class="h5 my-2">{{ $variant->name }}</div>
                                                <div>
                                                    <a href="{{ $variant->adminUrl }}" class="btn btn-sm btn-primary edit-variant"><i class="fas fa-cog mr-1"></i>Edit</a>
                                                    @if ($variant->dropData)
                                                        <a href="{{ url('/admin/data/pets/drops/edit') . '/' . $variant->id }}" class="btn btn-sm btn-primary"><i class="fas fa-gift mr-1"></i>Drops</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info">No variants found.</div>
                    @endif
                </div>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-header h2">Evolutions</div>
            <div class="card-body">
                <p>If you would like your pet to "evolve" (similarly to Pokémon), you can set up evolutions here. Evolutions are not required, and you can have as many or as few as you like. If you do not set up any evolutions, the pet will not evolve.
                </p>
                <p>Please note that variants will not be carried over to the evolved pet. If you would like to have a variant evolve into another variant, you will need to set up an evolution for each variant (after an evolution has been created).</p>
                <div class="mb-2 text-right">
                    <a href="#" class="btn btn-primary" id="add-evolution">Add Evolution</a>
                </div>
                @if ($pet->evolutions->count())
                    @foreach ($pet->evolutions->sortBy('evolution_stage')->chunk(4) as $chunk)
                        <div class="row {{ $loop->last ? '' : 'mb-3' }}">
                            @foreach ($chunk as $evolution)
                                <div class="col">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <a href="{{ $evolution->imageUrl }}" data-lightbox="entry" data-title="{{ $evolution->evolution_name }}">
                                                <img src="{{ $evolution->imageUrl }}" class="img-fluid" alt="{{ $evolution->evolution_name }}" data-toggle="tooltip" data-title="{{ $evolution->evolution_name }}" style="max-height:200px" />
                                            </a>
                                            <div class="h5 my-2">
                                                {{ $evolution->evolution_name }} (Stage {{ $evolution->evolution_stage }})
                                                @if (!$loop->last)
                                                    <i class="fas fa-arrow-right fa-lg mt-2"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="#" class="btn btn-sm btn-primary edit-evolution" data-id="{{ $evolution->id }}"><i class="fas fa-cog mr-1"></i>Edit</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info">No evolutions found.</div>
                @endif
            </div>
        </div>

        <h2>Preview</h2>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._pet_entry', ['pet' => $pet])
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @include('js._tinymce_wysiwyg')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-pet-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/pets/delete') }}/{{ $pet->id }}", 'Delete Pet');
            });

            $('#add-evolution').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/pets/edit/' . $pet->id . '/evolution/create') }}", 'Create Evolution');
            });

            $('.edit-evolution').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/pets/edit/' . $pet->id . '/evolution/edit') }}/" + $(this).data('id'), 'Edit Evolution');
            });
        });
    </script>
@endsection
