<div class="row flex-wrap">
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            <i class="inflow bg-success fas fa-arrow-up mr-2"></i>
            {!! $level->recipient ? $level->recipient->displayName : '' !!}
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="logs-table-cell">
            {!! $level->previous_level !!}
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="logs-table-cell">
            {!! $level->new_level !!}
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="logs-table-cell">
            {!! pretty_date($level->created_at) !!}
        </div>
    </div>
</div>