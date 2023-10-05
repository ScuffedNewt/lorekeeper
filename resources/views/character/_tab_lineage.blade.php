<div class="container text-center">
    @if ($character->children->count() && config('lorekeeper.lineage.show_children_on_tab'))
        @include('character._lineage_children', [
            'character' => $character,
            'max_depth' => 0,
            'title'     => 'Children',
        ])
    @endif
    <div class="row">
        @include('character._tab_lineage_col', [
            'character' => $character->lineage ? $character->lineage->father : null,
            'max_depth' => config('lorekeeper.lineage.lineage_depth') - 1,
            'parent' => 'Father',
        ])
        @include('character._tab_lineage_col', [
            'character' => $character->lineage ? $character->lineage->mother : null,
            'max_depth' => config('lorekeeper.lineage.lineage_depth') - 1,
            'parent' => 'Mother',
        ])
    </div>
</div>

@if(Auth::check() && Auth::user()->hasPower('manage_characters'))
    <div class="mt-3">
        <a href="#" class="btn btn-outline-info btn-sm edit-lineage" data-{{ $character->is_myo_slot ? 'id' : 'slug' }}="{{ $character->is_myo_slot ? $character->id : $character->slug }}"><i class="fas fa-cog"></i> Edit</a>
    </div>
@endif
