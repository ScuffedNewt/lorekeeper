<h1>
    Transfer Request (#{{ $transfer->id }})
    <span class="float-right badge badge-{{ $transfer->status == 'Pending' ? 'secondary' : ($transfer->status == 'Accepted' ? 'success' : 'danger') }}">{{ $transfer->status }}</span>
</h1>

<div class="mb-1">
    <div class="row">
        <div class="col-md-2 col-4"><h4>User</h4></div>
        <div class="col-md-10 col-8">{!! $transfer->user->displayName !!}</div>
    </div>
    <div class="row">
        <div class="col-md-2 col-4"><h4>Recipient</h4></div>
        <div class="col-md-10 col-8">{!! $transfer->recipient->displayname !!}</div>
    </div>
    <div class="row">
        <div class="col-md-2 col-4"><h4>Submitted</h4></div>
        <div class="col-md-10 col-8">{!! format_date($transfer->created_at) !!} ({{ $transfer->created_at->diffForHumans() }})</div>
    </div>
</div>

<div class="row col-12">
    <div class="col-2">
        <h2>Item</h2>
        <div class="card mb-3">
            <div class="card-body">
                @if($item->item->imageUrl)
                    <img src="{{ $item->item->imageUrl }}" data-toggle="tooltip" title="{{ $item->item->name }}">
                @else
                    {!! $item->item->displayName !!}
                @endif
                x {{ $quantity }}
            </div>
        </div>
    </div>
    <div class="col-10">
        <h2>Reason for Transfer</h2>
        <div class="card mb-3"><div class="card-body">{!! nl2br(htmlentities($transfer->reason)) !!}</div></div>
        @if(Auth::check() && $transfer->staff_comments && ($transfer->user_id == Auth::user()->id || Auth::user()->isStaff))
            <h2>Staff Comments ({!! $transfer->staff->displayName !!})</h2>
            <div class="card mb-3"><div class="card-body">
                {!! $transfer->staff_comments !!}
            </div></div>
        @endif
    </div>
</div>