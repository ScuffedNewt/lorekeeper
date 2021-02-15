@if($feature)
    {!! Form::open(['url' => 'admin/data/traits/delete/mass/'.$feature->id]) !!}

    <p>You are about to delete the trait <strong>{{ $feature->name }}</strong>. This is not reversible. If characters possessing this trait exist, the trait will be deleted.</p>
    <p>Are you sure you want to delete <strong>{{ $feature->name }}</strong>?</p>

    {!! Form::checkbox('im_sure', 1) !!} <p>I understand that by pressing this button that this trait will be removed from all characters. I understand once the process has started it cannot be reversed or undone</p>

    <div class="text-right">
        {!! Form::submit('Delete Trait', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid trait selected.
@endif