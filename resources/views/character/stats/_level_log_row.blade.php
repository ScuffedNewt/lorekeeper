@php($isLevelUp = $level->newLevel?->previous_level_id == $level->previous_level)
<div class="row flex-wrap">
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            <i class="{{ $isLevelUp ? 'in' : 'out' }}flow bg-{{ $isLevelUp ? 'success' : 'danger' }} fas {{ $isLevelUp ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-2"></i>
            {!! $level->sender ? $level->sender->displayName : 'System' !!}
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">{!! $level->recipient ? $level->recipient->displayName : '' !!}</div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">{{ $level->previousLevel?->name ?? 'Unknown Level' }}</div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">{{ $level->newLevel?->name ?? 'Unknown Level' }}</div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">{{ $level->log ?? 'Level Up' }}</div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">{!! pretty_date($level->created_at) !!}</div>
    </div>
</div>
