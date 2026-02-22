<div class="row flex-wrap">
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            <i class="{{ $stat->recipient_id == $owner->id && $stat->recipient_type == $owner->logType ? 'in' : 'out' }}flow bg-{{ $stat->quantity > 0 ? 'success' : 'danger' }} fas {{ $stat->quantity > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-2"></i>
            {!! $stat->sender ? $stat->sender->displayName : '' !!}
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            {!! $stat->recipient ? $stat->recipient->displayName : '' !!}
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            {{ $stat->quantity }}
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="logs-table-cell">
            {!! $stat->log !!}
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            {!! pretty_date($stat->created_at) !!}
        </div>
    </div>
</div>