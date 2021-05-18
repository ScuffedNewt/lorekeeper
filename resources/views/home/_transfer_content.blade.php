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
    <div class="col-6">
        @if($object->AssetType == 'user_items')
            <h2>Item</h2>
            <div class="card mb-3">
                <div class="card-body">
                    @if($object->item->imageUrl)
                        <img src="{{ $object->item->imageUrl }}" data-toggle="tooltip" title="{{ $object->item->name }}">
                    @else
                        {!! $object->item->displayName !!}
                    @endif
                    x {{ $quantity }}
                </div>
            </div>
        @else
        <h2>Currency</h2>
            <div class="card mb-3">
                <div class="card-body">
                    @if($object->currency->imageUrl)
                        <img src="{{ $object->currency->imageUrl }}" data-toggle="tooltip" title="{{ $object->currency->name }}">
                    @else
                        {!! $object->currency->displayName !!}
                    @endif
                    x {{ $quantity }}
                </div>
            </div>
        @endif
    </div>
    <div class="col-6">
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