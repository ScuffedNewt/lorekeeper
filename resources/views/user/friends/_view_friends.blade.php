@extends('user.layout')

@section('profile-title') {{ $user->name }}'s Friends @endsection

@section('profile-content')
{!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Friends' => 'friends']) !!}

<div class="card mb-3">
    <div class="card-header h3">Friends</div>
    <div class="table-responsive">
        @if($friends->where('recipient_approval', 'Approved')->count())
        <table class="table table-sm mb-0">
            <thead>
                <th></th>
            </thead>
            <tbody>
                @foreach($friends->where('recipient_approval', 'Approved') as $friend)
                    <tr>
                        <td class="text-center">{!! $friend->displayName($user->id) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="card-body">
            <p class="text-center">No friends (yet!).</p>
        @endif
    </div>
</div>

@endsection