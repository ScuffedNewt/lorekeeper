@if($referral)
    {!! Form::open(['url' => 'admin/data/referrals/delete/'.$referral->id]) !!}

    <p>You are about to delete the referral <strong>{{ $referral->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $referral->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Referral', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid referral selected.
@endif