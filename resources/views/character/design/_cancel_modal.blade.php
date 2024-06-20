@if ($request->user_id == Auth::user()->id)
    @if ($request->status != 'Draft')
        <p>This will cancel the request and allow you to edit it as a draft. </p>
        <p>Your position in the queue will not be preserved. Are you sure you want to cancel this request?</p>
        {!! Form::open(['url' => 'designs/' . $request->id . '/cancel', 'class' => 'text-right']) !!}
        {!! Form::submit('Cancel Request', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    @else
        <p class="text-danger">This request hasn't been submitted yet.</p>
    @endif
@else
    <div>You cannot delete this request.</div>
@endif
