@extends('home.layout')

@section('home-title')
    My Characters
@endsection

@section('home-content')
    {!! breadcrumbs(['My Characters' => 'characters']) !!}

    <h1>
        My Characters
    </h1>

    <div class="text-right mb-2">
        <a class="btn btn-primary create-folder mx-1" href="#"><i class="fas fa-plus"></i> Create New Folder</a>
        <a class="btn btn-primary edit-folder mx-1" href="#"><i class="fas fa-edit"></i> Edit Folder</a>
    </div>

    <div id="folders" class="collapse text-right">
        <div class="row">
            <div class="col-8">
            </div>
            <div class="form-group col-4">
                {!! Form::label('Select Folder to Edit') !!}
                {!! Form::select('folder_ids[]', $folders, null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="edit-get-button btn btn-primary"><i class="fas fa-edit"></i> Edit Folder</a></div>
    </div>


    <p>This is a list of characters you own. Drag and drop to rearrange them.</p>

    {!! Form::open(['url' => 'characters/sort', 'class' => 'text-right']) !!}
    <div id="sortable" class="row sortable">
        @foreach ($characters as $character)
            <div class="col-md-3 col-6 text-center mb-2" data-id="{{ $character->id }}">
                <div>
                    <a href="{{ $character->url }}"><img src="{{ $character->image->thumbnailUrl }}" class="img-thumbnail" alt="Thumbnail for {{ $character->fullName }}" /></a>
                </div>
                <div class="mt-1 h5">
                    {!! $character->displayName !!}
                </div>
                <div class="form-group">
                    {!! Form::label('folder_ids[]', 'Folder (Optional)') !!}
                    {!! Form::select('folder_ids[]', $folders, $character->folder_id, ['class' => 'form-control']) !!}
                </div>
            </div>
        @endforeach
    </div>
    {!! Form::hidden('sort', null, ['id' => 'sortableOrder']) !!}
    {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
    {!! Form::close() !!}

    <div class="mobile-handle handle-clone badge badge-primary rounded-circle hide">
        <i class="fas fa-hand-point-up" aria-hidden="true"></i>
        <span class="sr-only">Drag Handle</span>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('.create-folder').click(function(e){
                e.preventDefault();
                loadModal("{{ url('/characters/folder/create') }}", "Create New Folder");
            });

            $('.edit-folder').click(function(e){
                e.preventDefault();
                $('#folders').collapse('toggle');
            });

            $('.edit-get-button').click(function(e){
                e.preventDefault();
                var folder_id = $('#folders select').val();
                var url = "{{ url('/characters/folder/edit') }}/" + folder_id;
                loadModal(url, "Edit Folder");
            });

            $("#sortable").sortable({
                characters: '.sort-item',
                placeholder: "sortable-placeholder col-md-3 col-6",
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

            function isTouch() {
                try {
                    document.createEvent("TouchEvent");
                    return true;
                } catch (e) {
                    return false;
                }
            }

            if (isTouch()) {
                $('#sortable').children().each(function() {
                    var $clone = $('.handle-clone').clone();
                    $(this).append($clone);
                    $clone.removeClass('hide handle-clone');
                });
                $("#sortable").sortable("option", "handle", ".mobile-handle");
            }
        });
    </script>
@endsection
