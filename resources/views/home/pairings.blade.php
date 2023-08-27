@extends('home.layout')

@section('home-title') My Pairings @endsection

@section('home-content')
{!! breadcrumbs(['Characters' => 'characters', 'My Pairings' => 'myos']) !!}

<h1>
    My Pairings
</h1>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ !Request::get('type') || Request::get('type') == 'new' ? 'active' : '' }}" href="{{ url('characters/pairings') }}">New</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::get('type') == 'open' ? 'active' : '' }}" href="{{ url('characters/pairings') . '?type=open' }}">Open</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::get('type') == 'approval' ? 'active' : '' }}" href="{{ url('characters/pairings') . '?type=approval' }}">Approval</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::get('type') == 'closed' ? 'active' : '' }}" href="{{ url('characters/pairings') . '?type=closed' }}">Closed</a>
    </li>
</ul>

@if(!isset($pairings))
<p>Create a new pairing of characters. If you pair your character with one that belongs to another person, it is highly recommended you ask them first, as their approval will be needed.</p>
<div id="characters" class="mb-3">
        @if(isset($pairing_character_1))
        @include('widgets._character_select_entry', ['character' => $pairing_character_1])
        @endif
        @if(isset($pairing_character_2))
        @include('widgets._character_select_entry', ['character' => $pairing_character_2])
        @endif
</div>

{!! Form::open(['url' => url()->current(), 'id' => 'pairingForm']) !!}


<h2>Characters </h2>

<div id="characterComponents" class="row justify-content-center">
        <div class="submission-character m-3 card col-md" id="character_1">
            <div class="card-body">
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

<h2>Pairing Items </h2>
<p>
    Decide which pairing item and boosts to use. These items will be removed from your inventory but refunded if your pairing is rejected.
    For a successful pairing, you need to attach at least one valid Pairing Item. You can optionally attach Boost Items.
</p>
<div id="addons" class="mb-3">
        @include('widgets._inventory_select', ['user' => Auth::user(), 'inventory' => $inventory, 'categories' => $categories, 'selected' => [], 'page' => $page])
</div>

<div class="text-right">
    <a href="#" class="btn btn-secondary" id="pairingSubmit">Submit</a>
</div>

{!! Form::close() !!}

@else
{!! $pairings->render() !!}
<div class="table-responsive">
<table class="table">
    <thead>
    <tr>
      <th scope="col">Character 1</th>
      <th scope="col">Character 2</th>
      <th scope="col">Status</th>
      @if(Request::get('type') != 'closed')<th scope="col">Actions</th>@endif
      <th scope="col">Created</th>
    </tr>
  </thead>
  <tbody>
  @foreach($pairings as $pair)

    <tr>
      <td>
        <div class="row">
            <div class="col-lg-4"><a href="{{ $pair->character_1->url }}"><img class="w-100" src="{{ $pair->character_1->image->thumbnailUrl }}" style="max-width:100px;"/></a></div>
            <div class="col-lg-6">{{ $pair->character_1->slug }}</div>
        </div>
      </td>
      <td>
        <div class="row">
            <div class="col-lg-4"><a href="{{ $pair->character_2->url }}"><img class="w-100" src="{{ $pair->character_2->image->thumbnailUrl }}" style="max-width:100px;"/></a></div>
            <div class="col-lg-6">{{ $pair->character_2->slug }}</div>
        </div>
      </td>
      <td>            
        <span class="btn btn-{{ ($pair->status == 'OPEN' || $pair->status == 'USED' ) ? 'secondary' : ($pair->status == 'READY' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $pair->status }}</span>
      </td>
      <td>
      @if(Request::get('type') == 'open')
            @if($pair->status == 'READY')
                {!! Form::open(['url' => '/characters/pairings/myo', 'id' => 'myoForm']) !!}
                    {{ Form::hidden('pairing_id', $pair->id) }}
                    {!! Form::submit('Create MYO', ['class' => 'btn btn-secondary']) !!}
                {!! Form::close() !!}
            @else
                <a href="#" class="btn btn-secondary disabled" id="pairingMyo">Create MYO</a>
                {!! Form::open(['url' => '/characters/pairings/reject', 'id' => 'rejectForm']) !!}
                    {{ Form::hidden('pairing_id', $pair->id) }}
                    {!! Form::submit('Reject', ['class' => 'btn btn-danger']) !!}
                {!! Form::close() !!}
             @endif
        @endif
        @if(Request::get('type') == 'approval')
            {!! Form::open(['url' => '/characters/pairings/approve', 'id' => 'approveForm']) !!}
                {{ Form::hidden('pairing_id', $pair->id) }}
                {!! Form::submit('Approve', ['class' => 'btn btn-success']) !!}
            {!! Form::close() !!}

            {!! Form::open(['url' => '/characters/pairings/reject', 'id' => 'rejectForm']) !!}
                {{ Form::hidden('pairing_id', $pair->id) }}
                {!! Form::submit('Reject', ['class' => 'btn btn-danger']) !!}
            {!! Form::close() !!}
        @endif
      </td>
      <td>{!! pretty_date($pair->created_at) !!}</td>

    </tr>

  @endforeach

  </tbody>
</table>
</div>
{!! $pairings->render() !!}
@endif

@endsection
@section('scripts')
@parent
@include('widgets._inventory_select_js', ['readOnly' => true])

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
            }
        });
        $('.selectize').selectize();


    </script>

@endsection