@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s Links
@endsection

@section('meta-img')
    {{ $character->image->thumbnailUrl }}
@endsection

@section('profile-content')
    {!! breadcrumbs([
        $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
        $character->user->name . "'s characters" => '/user/' . $character->user->name . '/characters',
        $character->fullName => $character->url,
        'Profile' => $character->url . '/profile',
    ]) !!}

    <h2>
        {{ $character->fullName }}'s Links
        <a class="float-right mr-2" href="{{ url('reports/new?url=') . $character->url . '/links' }}">
            <i class="fas fa-exclamation-triangle text-danger" data-toggle="tooltip" title="Click here to report this character's links." style="opacity: 50%;"></i>
        </a>
    </h2>

    @if (Auth::check() && ($character->user_id == Auth::user()->id || Auth::user()->hasPower('manage_characters')))
        <div class="text-right m-2 mr-3 mt-3">
            <a href="{{ $character->url . '/links/edit' }}" class="btn btn-outline-info">
                <i class="fas fa-envelope"></i> Create Links For {!! $character->fullName !!}
            </a>
        </div>
    @endif

    @if (count($links))
        <div class="container mt-4" @if ($canManage) id="sortable" @endif>
            @foreach ($links as $link)
                @if ($link->getRelationshipVisibility($character->id) === 1 || $canManage)
                    <div class="card character-bio mb-2 sort-item" data-id="{{ $link->id }}">

                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    @include('character._link_character', ['character' => $link->getCharacter($character->id)])
                                </div>
                                <div class="col-6">
                                    @include('character._link_character', ['character' => $link->getOtherCharacter($character->id)])
                                </div>
                            </div>
                            @if ($canManage)
                                <div class="row mt-2">
                                    <div class="d-flex align-items-center col-1 justify-content-end" style="font-size: larger;">
                                        <i class="fas fa-arrows-alt-v handle mr-3"></i>
                                        @if (!$link->getRelationshipVisibility($character->id))
                                            <i class="fas fa-eye-slash mr-1"></i>
                                        @endif
                                    </div>
                                    <ul class="col-11 nav nav-tabs card-header-tabs">
                                        <li class="nav-item ml-4">
                                            <a class="nav-link active" id="viewTab-{{ $link->id }}" data-toggle="tab" role="tab" href="#view-{{ $link->id }}">Info</a>
                                        </li>
                                        <li class="nav-item ml-4">
                                            <a class="nav-link" id="editTab-{{ $link->id }}" data-toggle="tab" role="tab" href="#edit-{{ $link->id }}">Edit</a>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="view-{{ $link->id }}">
                                <div class="row no-gutters">
                                    <div class="col-12 col-md-6 p-3">
                                        <div class="text-center text-uppercase mb-0">Views {!! $link->getOtherCharacter($character->id)->displayName !!} as:</div>
                                        <h6 class="text-center text-uppercase"><b>{!! $link->getRelationshipType($character->id) !!}</b></h5>
                                            <div>{!! $link->getRelationshipParsedInfo($character->id) !!}</div>
                                    </div>
                                    <div class="col-12 col-md-6 p-3">
                                        <div class="text-center text-uppercase mb-0">Views {!! $character->displayName !!} as:</div>
                                        <h6 class="text-center text-uppercase"><b>{!! $link->getRelationshipType($link->getOtherCharacter($character->id)->id) !!}</b></h5>
                                            <div>{!! $link->getRelationshipParsedInfo($link->getOtherCharacter($character->id)->id) !!}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="edit-{{ $link->id }}">
                                <div class="col-12">
                                    <div class="mb-md-0 mb-2">
                                        <div class="m-2">
                                            @if (Auth::check() && ($character->user_id == Auth::user()->id || Auth::user()->hasPower('manage_characters')))
                                                {!! Form::open(['url' => $character->url . '/links/info/' . $link->id]) !!}

                                                <div class="form-group">
                                                    {!! Form::label('type', 'Relationship Type') !!} {!! add_help('What type of relationship do these characters have?') !!}
                                                    {!! Form::select('type', $types, $link->type, ['class' => 'form-control mt-2', 'placeholder' => 'Relationship Type']) !!}
                                                </div>

                                                <div class="form-group">
                                                    {!! Form::label('type', 'Relationship Info') !!} {!! add_help('What are your characters feelings?') !!}
                                                    {!! Form::textarea('info', $link->getRelationshipInfo($character->id) ?? null, ['placeholder' => '', 'class' => 'form-control mb-2 wysiwyg', 'cols' => 20, 'rows' => 5]) !!}
                                                </div>

                                                <div class="form-group text-right">
                                                    {!! Form::checkbox('is_visible', 1, $link->getRelationshipVisibility($character->id) ?? null, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                                                    {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Turn this off to hide this side of the relationship.') !!}
                                                </div>

                                                <div class="text-right m-2 d-flex justify-content-between align-items-center">
                                                    @if (Auth::check() && ($character->user_id == Auth::user()->id || Auth::user()->hasPower('manage_characters')))
                                                        <div class="btn btn-danger btn-sm m-1 delete-button" data-id="{{ $link->id }}">
                                                            <i class="fas fa-trash"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        {!! Form::button('<i class="fas fa-cog"></i> Edit Info', ['class' => 'btn btn-outline-info btn-sm', 'type' => 'submit']) !!}
                                                    </div>
                                                </div>
                                                {!! Form::close() !!}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        @if ($canManage)
            <div class="text-right m-2 mr-3 mt-3">
                {!! Form::open(['url' => 'character/' . $character->slug . '/links/sort']) !!}
                {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
                {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
                {!! Form::close() !!}
            </div>
        @endif
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> This character has no relations.
        </div>
    @endif
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('.delete-button').click(function() {
                loadModal("{{ $character->url }}/links/delete/" + $(this).data('id'), 'Delete Link?');
            });
        });

        @if ($canManage)
            $("#sortable").sortable({
                characters: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        @endif
    </script>
@endsection
