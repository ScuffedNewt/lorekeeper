@extends('user.layout')

@section('profile-title') {{ $user->name }}'s @if($pet->has_image) Custom @endif {{ $pet->pet->name }}  @endsection

@section('profile-content')

@if($pet->has_image)
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Pets' => $user->url . '/pets', $user->name .'\'s custom '. $pet->pet->name => 'pet' ]) !!}
@else
{!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Pets' => $user->url . '/pets', $user->name .'\'s '. $pet->pet->name => 'pet' ]) !!}
@endif


<h1>
{{ $user->name }}'s @if($pet->has_image) Custom @endif {{ $pet->pet->name }}
</h1>

@if(Auth::check() && ($pet->user_id !== Auth::user()->id && Auth::user()->hasPower('edit_inventories')))
    <div class="alert alert-warning">
        You are editing this pet as a staff member.
    </div>
@endif



<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 text-center align-self-center">
                <h2>{{ $pet->pet_name }}</h2>
                <img src="{{  $pet->imageUrl }}">
            </div>
            <div class="col-md text-center">
            @if($pet->has_image)
                <div class="mt-2">
                    <h3>
                       Artist
                    </h3>
                    @if(isset($pet->petArtist) && $pet->petArtist)
                        This pet is displaying custom art!
                        <div class="col-md">
                            <p><strong>Artist:</strong> {!! $pet->petArtist !!}</p>
                        </div>
                    @else
                        No credits given.
                    @endif
                </div>
                @endif
                @if($pet->description)
                <br>
                <hr>
                <h3>Biography</h3>
                <div class="card mb-3">
                    <div class="card-body parsed-text">
                            {!! $pet->description !!}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    

    @if(Auth::check() && ($pet->user_id == Auth::user()->id || Auth::user()->hasPower('edit_inventories')))
    <li class="list-group-item">
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#nameForm">@if($pet->user_id != Auth::user()->id) [ADMIN] @endif Name Pet</a>
                    {!! Form::open(['url' => 'pets/name/'.$pet->id, 'id' => 'nameForm', 'class' => 'collapse']) !!}
                        <p>Enter a name to display for the pet!</p>
                        <div class="form-group">
                            {!! Form::label('name', 'Name') !!} {!! add_help('If your name is not appropriate you can be banned.') !!}
                            {!! Form::text('name', null, ['class'=>'form-control']) !!}
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Name', ['class' => 'btn btn-primary']) !!}
                        </div>
                    {!! Form::close() !!}
                </li>
                @endif
                @if(Auth::check() && ($pet->user_id == Auth::user()->id || Auth::user()->hasPower('edit_inventories')))
    <li class="list-group-item">
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#descForm">@if($pet->user_id != Auth::user()->id) [ADMIN] @endif Edit Profile</a>
                    {!! Form::open(['url' => 'pets/desc/'.$pet->id, 'id' => 'descForm', 'class' => 'collapse']) !!}
                        <p>Tell everyone about your pet.</p>
                        <div class="form-group">
                        {!! Form::label('Profile Text (Optional)') !!}
                        {!! Form::textarea('description', $pet->description, ['class' => 'form-control wysiwyg']) !!}
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                        </div>
                    {!! Form::close() !!}
                </li>
                @endif
    @if(Auth::check() && Auth::user()->hasPower('edit_inventories'))
                    <li class="list-group-item">
                        <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#imageForm">[ADMIN] Change Image</a>
                        {!! Form::open(['url' => 'pets/image/'.$pet->id, 'id' => 'imageForm', 'class' => 'collapse', 'files' => true]) !!}
                            <div class="form-group">
                            {!! Form::label('Image') !!}
                            <div>{!! Form::file('image') !!}</div>
                            <div class="text-muted">Recommended size: 100px x 100px</div>
                            @if($pet->has_image)
                                <div class="form-check">
                                    {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                                    {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                                </div>
                            @endif
                        </div>
                                    <div class="col-md">
                    {!! Form::label('Pet Artist (Optional)') !!} {!! add_help('Provide the artist\'s username if they are on site or, failing that, a link.') !!}
                        <div class="row">
                            <div class="col-md">
                                <div class="form-group">
                                    {!! Form::select('artist_id', $userOptions, $pet->artist_id ? $pet->artist_id : null, ['class'=> 'form-control mr-2 selectize']) !!}
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="form-group">
                                    {!! Form::text('artist_url', $pet->artist_url ? $pet->artist_url : '', ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
                                </div>
                            </div>
                        </div>
                        @if($pet->has_image)
                                <div class="form-check">
                                    {!! Form::checkbox('remove_credit', 1, false, ['class' => 'form-check-input']) !!}
                                    {!! Form::label('remove_credit', 'Remove current credits', ['class' => 'form-check-label']) !!}
                                </div>
                            @endif
                    </div>
                        <div class="text-right">
                            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                        </div>
                        {!! Form::close() !!}
                    </li>
                @endif

@endsection