@extends('home.layout')

@section('home-title') My Pairings @endsection

@section('home-content')
{!! breadcrumbs(['Characters' => 'characters', 'My Pairings' => 'myos']) !!}

<h1>
    My Pairings
</h1>

<h3>Create</h3>
<p>Create a new pairing of characters. This will show up on other users pairing pages if you enter a character that does not belong to you.</p>
<div id="characters" class="mb-3">
        @if(isset($pairing_character_1))
        @include('widgets._character_select_entry', ['character' => $pairing_character_1])
        @endif
        @if(isset($pairing_character_2))
        @include('widgets._character_select_entry', ['character' => $pairing_character_2])
        @endif

</div>

@if(count($items) > 0)
{!! Form::open(['url' => url()->current(), 'id' => 'pairingForm']) !!}
{!! Form::label('Pairing Item') !!}
{!! Form::select('item_id', $items, $item->item_id ?? null, ['class' => 'form-control selectize']) !!}
<div id="characterComponents" class="row justify-content-center">
        <div class="submission-character m-3 card col-md" id="character_1">
            <div class="card-body">
                <div class="text-right"><a href="#" class="remove-character text-muted"><i class="fas fa-times"></i></a></div>
                <div class="row">
                    <div class="col-md-4 align-items-stretch d-flex">
                        <div class="d-flex text-center align-items-center">
                            <div class="character-image-blank">Enter character code.</div>
                            <div class="character-image-loaded hide"></div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <a href="#" class="float-right fas fa-close"></a>
                        <div class="form-group">
                            {!! Form::label('character_1_code', 'Character Code 1') !!}
                            {!! Form::text('character_1_code', null, ['class' => 'form-control character-code']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="submission-character m-3 card col-md" id="character_2">
            <div class="card-body">
                <div class="text-right"><a href="#" class="remove-character text-muted"><i class="fas fa-times"></i></a></div>
                <div class="row">
                    <div class="col-md-4 align-items-stretch d-flex">
                        <div class="d-flex text-center align-items-center">
                            <div class="character-image-blank">Enter character code.</div>
                            <div class="character-image-loaded hide"></div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <a href="#" class="float-right fas fa-close"></a>
                        <div class="form-group">
                            {!! Form::label('character_2_code', 'Character Code 2') !!}
                            {!! Form::text('character_2_code', null, ['class' => 'form-control character-code']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
<div class="text-right">
    <a href="#" class="btn btn-secondary" id="pairingSubmit">Submit</a>
</div>
{!! Form::close() !!}
@else
<p class="p-2 text-danger border">It seems you do not currently own any items used for creating pairings.</p>
@endif 

<h3>Open</h3>

<p>This is a list of Pairings you created that may still await approval or are ready to be turned into a MYO slot!</p>
@foreach($pairings as $pair)
<div class="row">
    <div class="col-sm text-center mb-2">
        <div class="row">
            <div class="col-5">
                <div>
                    <a href="{{ $pair->character_1->url }}"><img class="w-25" src="{{ $pair->character_1->image->thumbnailUrl }}" class="img-thumbnail" /></a>
                </div>
                <p>{{ $pair->character_1->slug }}</p>
            </div>
            <div class="col-1 text-center p-4">
            <h2> + </h2>
            </div>
            <div class="col-5">
                <div>
                    <a href="{{ $pair->character_2->url }}"><img class="w-25" src="{{ $pair->character_2->image->thumbnailUrl }}" class="img-thumbnail" /></a>
                </div>
                <p>{{ $pair->character_2->slug }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="row p-4">
            <div class="col m-auto">
            Status:
            <h4>{{ $pair->status }}</h4>
            </div>
            <div class="col m-auto">
                @if($pair->status == 'READY')
                {!! Form::open(['url' => '/characters/pairings/myo', 'id' => 'myoForm']) !!}
                {{ Form::hidden('pairing_id', $pair->id) }}
                {!! Form::submit('Create MYO', ['class' => 'btn btn-secondary']) !!}
                {!! Form::close() !!}
                @else
                <a href="#" class="btn btn-secondary disabled" id="pairingMyo">Create MYO</a>
                @endif
            </div>
        </div>
    </div>
</div>
<hr>
@endforeach

<h3>Approvals</h3>

<p>This is a list of Pairings your characters were requested to be part of. You can approve or decline them here.</p>
@foreach($approvals as $pair)
<div class="row">
    <div class="col-sm text-center mb-2">
        <div class="row">
            <div class="col-5">
                <div>
                    <a href="{{ $pair->character_1->url }}"><img class="w-25" src="{{ $pair->character_1->image->thumbnailUrl }}" class="img-thumbnail" /></a>
                </div>
                <p>{{ $pair->character_1->slug }}</p>
            </div>
            <div class="col-1 text-center p-4">
            <h2> + </h2>
            </div>
            <div class="col-5">
                <div>
                    <a href="{{ $pair->character_2->url }}"><img class="w-25" src="{{ $pair->character_2->image->thumbnailUrl }}" class="img-thumbnail" /></a>
                </div>
                <p>{{ $pair->character_2->slug }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="row p-4">
            <div class="col m-auto">
            Status:
            <h4>{{ $pair->status }}</h4>
            </div>
            <div class="col m-auto">
                <div class="row">
                    {!! Form::open(['url' => '/characters/pairings/approve', 'id' => 'approveForm']) !!}
                    {{ Form::hidden('pairing_id', $pair->id) }}
                    {!! Form::submit('Approve', ['class' => 'btn btn-success']) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['url' => '/characters/pairings/reject', 'id' => 'rejectForm']) !!}
                    {{ Form::hidden('pairing_id', $pair->id) }}
                    {!! Form::submit('Reject', ['class' => 'btn btn-danger']) !!}
                    {!! Form::close() !!}
                </div>


            </div>
        </div>
    </div>
</div>
<hr>
@endforeach


<h3>Closed</h3>

<p>Pairings that have been turned into a MYO slot or were rejected.</p>
@foreach($closed as $pair)
<div class="row">
    <div class="col-sm text-center">
        <div class="row">
            <div class="col-5">
                <div>
                    <a href="{{ $pair->character_1->url }}">{{ $pair->character_1->slug }}</a>
                </div>
            </div>
            <div class="col-1 text-center">
            <h5> + </h5>
            </div>
            <div class="col-5">
                <div>
                    <a href="{{ $pair->character_2->url }}">{{ $pair->character_2->slug }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="row">
            <div class="col">
            <h5>{{ $pair->status }}</h5>
            </div>
        </div>
    </div>
</div>
<hr>
@endforeach

@endsection

@section('scripts')
@parent

    <script>

        $(document).ready(function() {
            var $char_1 = $('#character_1');
            var $char_2 = $('#character_2');

            attachListeners($char_1)
            attachListeners($char_2)

            var $pairingForm = $('#pairingForm');
            var $pairingSubmit = $('#pairingSubmit');


            $pairingSubmit.on('click', function(e) {
                e.preventDefault();
                $pairingForm.submit();
            });


            function attachListeners(node) {
                node.find('.character-code').on('change', function(e) {
                    var $parent = $(this).parent().parent().parent().parent();
                    $parent.find('.character-image-loaded').load('{{ url('submissions/new/character') }}/'+$(this).val(), function(response, status, xhr) {
                        $parent.find('.character-image-blank').addClass('hide');
                        $parent.find('.character-image-loaded').removeClass('hide');
                        $parent.find('.character-rewards').removeClass('hide');
                    });
                });
                node.find('.remove-character').on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().parent().remove();
                });
            }
        });
        $('.selectize').selectize();


    </script>

@endsection