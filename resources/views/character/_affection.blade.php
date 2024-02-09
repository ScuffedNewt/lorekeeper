@php
    $affection = $user->npcAffections->where('npc_id', $character->id)->first();
    if (!$affection) {
        $affection = $user->npcAffections()->create([
            'character_id' => $character->id,
            'affection' => $character->npcInformation->default_affection ?? 0,
        ]);
    }
@endphp

{{-- progress bar --}}
<div class="progress mb-3">
    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $affection->affection }}%" aria-valuenow="{{ $affection->affection }}" aria-valuemin="0" aria-valuemax="100">
        {{ $affection->affection }}% Affection
    </div>
</div>
