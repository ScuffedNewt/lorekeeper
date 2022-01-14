@if(Auth::check() && Auth::user()->id == $user->id)
    @include('user.friends._self_friends', ['friends' => $friends, 'user' => $user])
@else
    @include('user.friends._view_friends', ['friends' => $friends, 'user' => $user])
@endif