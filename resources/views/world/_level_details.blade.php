<dl class="row mb-3">
    <dt class="col-sm-3">EXP Required</dt>
    <dd class="col-sm-9">
        @if (!$level->previous_level_id)
            Starting level.
        @elseif ($level->exp_required === null)
            <span class="badge badge-info">{{ hasLimits($level) ? 'No EXP: Complete Requirements' : 'Rewards / Grants Only' }}</span>
        @else
            {{ number_format($level->exp_required) }} EXP
        @endif
    </dd>
</dl>
@if (!$level->previous_level_id)
    <p>This is the starting level. It does not require any EXP to obtain and can be granted freely.</p>
@elseif ($level->exp_required === null)
    @if (hasLimits($level))
        <p>Advance from the previous level by completing the requirements below. No EXP is needed. This level can also be credited through rewards or grants.</p>
    @else
        <p>This level must be credited through a reward or grant. EXP cannot be used to obtain it.</p>
    @endif
@else
    <p>Advance from the previous level by spending {{ number_format($level->exp_required) }} EXP and meeting the requirements below. This level can also be credited through rewards or grants without spending EXP.</p>
@endif
