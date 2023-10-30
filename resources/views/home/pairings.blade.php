@extends('home.layout')

@section('home-title') My Pairings @endsection

@section('home-content')
    {!! breadcrumbs(['Characters' => 'characters', 'My Pairings' => 'myos']) !!}

    <h1>
        My Pairings
    </h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ !Request::get('type') || Request::get('type') == 'new' ? 'active' : '' }}" href="{{ url('characters/pairings') }}">Create New</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::get('type') == 'approval' ? 'active' : '' }}" href="{{ url('characters/pairings') . '?type=approval' }}">Approval Required</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::get('type') == 'waiting' ? 'active' : '' }}" href="{{ url('characters/pairings') . '?type=waiting' }}">Waiting</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::get('type') == 'closed' ? 'active' : '' }}" href="{{ url('characters/pairings') . '?type=closed' }}">Closed</a>
        </li>
    </ul>

    @if(!isset($pairings))
        @include('home._create_pairing', [
            'characters' => $characters,
            'user_pairing_items' => $user_pairing_items,
            'pairing_item_filter' => $pairing_item_filter,
            'user_boost_items' => $user_boost_items,
            'boost_item_filter' => $boost_item_filter,
            'categories' => $categories,
            'page' => $page,
        ])
    @else
    {!! $pairings->render() !!}
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th width="15%">Character 1</th>
                        <th width="15%">Character 2</th>
                        <th width="25%">Items</th>
                        <th>Status</th>
                        @if(Request::get('type') != 'closed')
                            <th width="20%">Actions</th>
                        @endif
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pairings as $pair)
                        @include('home._pairing_row', ['pair' => $pair])
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

            function attachListeners(node) {
                node.find('.character-code').on('change', function(e) {
                    var $parent = $(this).parent().parent().parent().parent();
                    console.log($parent, $(this).val());
                    $parent.find('.character-image-loaded').load('{{ url('submissions/new/character') }}/' + $(this).val(),
                    function(response, status, xhr) {
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
