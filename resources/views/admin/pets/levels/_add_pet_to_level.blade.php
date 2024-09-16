{!! Form::open(['url' => 'admin/data/pets/levels/edit/' . $level->id . '/pets/add']) !!}

<div class="alert alert-info mt-0">
    You can add rewards to a pet once it has been added.
</div>

<div class="btn btn-primary add-pet mb-2">
    <i class="fas fa-plus"></i> Add Another Pet
</div>

@foreach ($level->pets as $pet)
    <div class="form-group">
        {!! Form::select('pet_ids[]', $pets, $pet->pet_id, ['class' => 'form-control']) !!}
    </div>
@endforeach

<div id="pets">
</div>

<div class="text-right">
    {!! Form::submit('Add Pets', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<div class="form-group pet-row hide">
    {!! Form::select('pet_ids[]', $pets, null, ['class' => 'form-control', 'placeholder' => 'Select Pet']) !!}
</div>

<script>
    $(document).ready(function() {
        $('.add-pet').on('click', function(e) {
            e.preventDefault();
            let petRow = $('.pet-row').clone();
            petRow.removeClass('hide').removeClass('pet-row');
            petRow.appendTo('#pets');
        });
    });
</script>
