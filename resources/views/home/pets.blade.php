@extends('home.layout')

@section('home-title')
    Pets
@endsection

@section('home-content')
    {!! breadcrumbs(['Pets' => 'pets']) !!}

    <h1>
        Pets
    </h1>

    <p>These are your pets. Click on a pet to view more details and actions you can perform on it.</p>

    <div class="text-right">
        {!! Form::open(['url' => 'pets/collect-all']) !!}
        {!! Form::submit('Collect All Pet Drops', ['class' => 'btn btn-success my-3']) !!}
        {!! Form::close() !!}
    </div>

    @foreach ($pets as $categoryId => $categoryPets)
        <div class="card mb-3 inventory-category">
            <h5 class="card-header inventory-header">
                {!! isset($categories[$categoryId]) ? '<a href="' . $categories[$categoryId]->searchUrl . '">' . $categories[$categoryId]->name . '</a>' : 'Miscellaneous' !!}
            </h5>
            <div class="card-body inventory-body">
                @foreach ($categoryPets->chunk(4) as $chunk)
                    <div class="row mb-3">
                        @foreach ($chunk as $pet)
                            <div class="col-sm-3 col-6 text-center inventory-pet" data-id="{{ $pet->pivot->id }}" data-name="{{ $user->name }}'s {{ $pet->name }}">
                                <div class="mb-1">
                                    <a href="#" class="inventory-stack"><img src="{{ $pet->image($pet->pivot->id) }}" class="img-fluid" /></a>
                                </div>
                                <div>
                                    <a href="{{ url('pets/view/' . $pet->pivot->id) }}" class="{{ $pet->pivot->pet_name ? 'btn-dark' : 'btn-primary' }} btn btn-sm my-1">
                                        {!! $pet->pivot->pet_name ?? ($pet->pivot->evolution_id ? $pet->evolutions->where('id', $pet->pivot->evolution_id)->first()->evolution_name : $pet->name) !!}
                                        @if ($pet->pivot->has_image)
                                            <i class="fas fa-brush ml-1" data-toggle="tooltip" title="This pet has custom art."></i>
                                        @endif
                                        @if ($pet->pivot->character_id)
                                            <span data-toggle="tooltip" title="Attached to {!! strip_tags(getDisplayName(\App\Models\Character\Character::class, $pet->pivot->character_id)) !!}"><i class="fas fa-link ml-1"></i></span>
                                        @endif
                                        @if ($pet->pivot->evolution_id)
                                            <span data-toggle="tooltip" title="This pet has evolved. Stage
                                            {{ $pet->evolutions->where('id', $pet->pivot->evolution_id)->first()->evolution_stage }}."><i
                                                    class="fas fa-angle-double-up ml-1"></i>
                                            </span>
                                        @endif
                                    </a>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
    <div class="text-right mb-4">
        <a href="{{ url(Auth::user()->url . '/pet-logs') }}">View logs...</a>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('.inventory-stack').on('click', function(e) {
                e.preventDefault();
                var $parent = $(this).parent().parent();
                loadModal("{{ url('pets') }}/" + $parent.data('id'), $parent.data('name'));
            });
        });
    </script>
@endsection
