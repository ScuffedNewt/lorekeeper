@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}
@endsection

@section('meta-img')
    {{ $character->image->thumbnailUrl }}
@endsection

@section('profile-content')
    @if ($character->is_myo_slot)
        {!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url]) !!}
    @else
        {!! breadcrumbs([
            $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
            $character->fullName => $character->url,
        ]) !!}
    @endif

    @include('character._header', ['character' => $character])

    @if (Auth::check())
        @include('character._affection', ['character' => $character, 'user' => Auth::user()])
    @endif

    {{-- Main Image --}}
    <div class="row mb-3">
        <div class="col-md-7">
            <div class="text-center">
                <a href="{{ $character->image->canViewFull(Auth::check() ? Auth::user() : null) && file_exists(public_path($character->image->imageDirectory . '/' . $character->image->fullsizeFileName)) ? $character->image->fullsizeUrl : $character->image->imageUrl }}"
                    data-lightbox="entry" data-title="{{ $character->fullName }}">
                    <img src="{{ $character->image->canViewFull(Auth::check() ? Auth::user() : null) && file_exists(public_path($character->image->imageDirectory . '/' . $character->image->fullsizeFileName)) ? $character->image->fullsizeUrl : $character->image->imageUrl }}"
                        class="image" alt="{{ $character->fullName }}" />
                </a>
            </div>
            @if ($character->image->canViewFull(Auth::check() ? Auth::user() : null) && file_exists(public_path($character->image->imageDirectory . '/' . $character->image->fullsizeFileName)))
                <div class="text-right">You are viewing the full-size image. <a href="{{ $character->image->imageUrl }}">View watermarked image</a>?</div>
            @endif
        </div>
        @include('character._image_info', ['image' => $character->image])
    </div>

    {{-- Info --}}
    <div class="card character-bio">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" id="statsTab" data-toggle="tab" href="#stats" role="tab">Stats</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="notesTab" data-toggle="tab" href="#notes" role="tab">Description</a>
                </li>
                @if (Auth::check() && Auth::user()->hasPower('manage_characters'))
                    <li class="nav-item">
                        <a class="nav-link" id="settingsTab" data-toggle="tab" href="#settings-{{ $character->slug }}" role="tab"><i class="fas fa-cog"></i></a>
                    </li>
                @endif
            </ul>
        </div>
        <div class="card-body tab-content">
            <div class="tab-pane fade show active" id="stats">
                @include('character._tab_stats', ['character' => $character])
            </div>
            <div class="tab-pane fade" id="notes">
                @include('character._tab_notes', ['character' => $character])
            </div>
            @if (Auth::check() && Auth::user()->hasPower('manage_characters'))
                <div class="tab-pane fade" id="settings-{{ $character->slug }}">
                    {!! Form::open(['url' => $character->is_myo_slot ? 'admin/myo/' . $character->id . '/settings' : 'admin/character/' . $character->slug . '/settings']) !!}
                    <div class="form-group">
                        {!! Form::checkbox('is_visible', 1, $character->is_visible, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Turn this off to hide the character. Only mods with the Manage Masterlist power (that\'s you!) can view it - the owner will also not be able to see the character\'s page.') !!}
                    </div>
                    <div class="text-right">
                        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
                    </div>
                    {!! Form::close() !!}
                    <hr />
                    @if (!$character->is_myo_slot)
                        {!! Form::open(['url' => 'admin/character/' . $character->slug . '/npc']) !!}

                        <div class="form-group">
                            {!! Form::checkbox('is_npc', 1, $character->is_npc, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'npc']) !!}
                            {!! Form::label('is_npc', 'Is NPC', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Turn this on to mark the character as an NPC. This will modify the character\'s page to present as an NPC.') !!}
                        </div>

                        @if ($character->is_npc)
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        {!! Form::label('default_affection', 'Default Affection') !!} {!! add_help('The default affection level for this NPC, on a scale of 0-100.') !!}
                                        {!! Form::number('default_affection', $character->npcInformation?->default_affection ?? 0, ['class' => 'form-control', 'min' => 0, 'max' => 100]) !!}
                                    </div>
                                    <div class="col-md-6">
                                        {!! Form::label('biography_affection_requirement', 'Biography Affection Requirement') !!} {!! add_help('The affection level required to view the biography of this NPC.') !!}
                                        {!! Form::number('biography_affection_requirement', $character->npcInformation?->biography_affection_requirement ?? 50, ['class' => 'form-control', 'min' => 0, 'max' => 100]) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- biography, wisywg editor --}}
                            <div class="form-group">
                                {!! Form::label('biography', 'Biography') !!} {!! add_help('The biography of the NPC. Biographys become available when affection is above 50.') !!}
                                {!! Form::textarea('biography', $character->npcInformation?->biography, ['class' => 'form-control wysiwyg', 'rows' => 5]) !!}
                            </div>
                        @endif
                        <div class="text-right">
                            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
                        </div>
                        {!! Form::close() !!}
                        <hr />
                    @endif
                    <div class="text-right">
                        <a href="#" class="btn btn-outline-danger btn-sm delete-character" data-slug="{{ $character->slug }}">Delete</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    @include('character._image_js', ['character' => $character])
@endsection
