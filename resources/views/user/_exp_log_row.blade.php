<div class="row flex-wrap">
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            <i class="{{ $exp->recipient_id == $owner->id && $exp->recipient_type == $owner->logType ? 'in' : 'out' }}flow bg-{{ $exp->quantity > 0 ? 'success' : 'danger' }} fas {{ $exp->quantity > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-2"></i>
            {!! $exp->sender ? $exp->sender->displayName : '' !!}
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            {!! $exp->recipient ? $exp->recipient->displayName : '' !!}
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            {{ $exp->quantity }}
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="logs-table-cell">
            {!! $exp->log !!}
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            {!! pretty_date($exp->created_at) !!}
        </div>
    </div>
</div>
