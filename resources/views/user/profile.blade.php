@extends('user.layout')

@section('profile-title') {{ $user->name }}'s Profile @endsection

@section('profile-content')
{!! breadcrumbs(['Users' => 'users', $user->name => $user->url]) !!}

@if($user->is_banned)
    <div class="alert alert-danger">This user has been banned.</div>
@endif


 <h1>

   <!-- If you install the user icon extension: the icon goes here:

  <img src="/images/avatars/{{ $user->avatar }}" style="width:125px; height:125px; float:left; border-radius:50%; margin-right:25px;">

  -->


 @if($user->settings->is_fto)
         <span class="badge badge-success float-right" data-toggle="tooltip" title="This user has not owned any characters from this world before.">FTO</span>
 @endif

 <div class="row">
   <div style="padding-right: 10px;">{!! $user->displayName !!}</div>

   <div class="ulinks" style="padding-top:7px">

    @if($user->aliases()->where('site', 'deviantart')->exists())
        @php $dA = $user->aliases()->where('site', 'deviantart')->first(); @endphp
        <a class="float px-1" data-toggle="tooltip" title=" {!! $dA->alias !!}&#64;deviantart" href="{!! $dA->Url !!}"><i class="fab fa-deviantart fa-lg"></i></a>
    @else
        <a class="float px-1" data-toggle="tooltip" title=" Unverified "><i class="fab fa-deviantart fa-lg text-danger"></i></a>
    @endif
    @if($user->aliases()->where('site', 'twitter')->exists())
        @php $twitter = $user->aliases()->where('site', 'twitter')->first(); @endphp
        <a class="float px-1" data-toggle="tooltip" title=" {!! $twitter->alias !!}&#64;twitter" href="{!! $twitter->Url !!}"><i class="fab fa-twitter fa-lg"></i></a>
    @else
        <a class="float px-1" data-toggle="tooltip" title=" Unverified "><i class="fab fa-deviantart fa-lg text-danger"></i></a>
    @endif
    @if($user->profile->house)
        <a class="float px-1" data-toggle="tooltip" title=" {!! $user->profile->house !!}&#64;toyhou.se" href="https://toyhou.se/{!! $user->profile->house !!}"><i class="fas fa-home fa-lg"></i></a></span>
    @endif
    @if($user->profile->insta)
        <a class="float px-1" data-toggle="tooltip" title=" {!! $user->profile->insta !!}&#64;instagram" href="https://www.instagram.com/{!! $user->profile->insta !!}"><i class="fab fa-instagram fa-lg"></i></a></span>
    @endif
    @if($user->profile->arch)
        <a class="float px-1" data-toggle="tooltip" title=" {!! $user->profile->arch !!}&#64;archive" href="https://archiveofourown.org/users/{!! $user->profile->arch !!}"><i class="fas fa-file-alt fa-lg"></i></a></span>
    @endif

 </div>
 </div>

 </h1>
<div class="mb-1">
    <div class="row">
        <div class="col-md-2 col-4"><h5>Alias</h5></div>
        <div class="col-md-10 col-8">{!! $user->displayAlias !!}</div>
    </div>
    <div class="row">
        <div class="col-md-2 col-4"><h5>Rank</h5></div>
        <div class="col-md-10 col-8">{!! $user->rank->displayName !!} {!! add_help($user->rank->parsed_description) !!}</div>
    </div>
    <div class="row">
        <div class="col-md-2 col-4"><h5>Joined</h5></div>
        <div class="col-md-10 col-8">{!! format_date($user->created_at, false) !!} ({{ $user->created_at->diffForHumans() }})</div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        {!! $user->profile->parsed_text !!}
    </div>
</div>

<div class="card-deck mb-4 profile-assets">
    <div class="card profile-currencies profile-assets-card">
        <div class="card-body text-center">
            <h5 class="card-title">Bank</h5>
            <div class="profile-assets-content">
                @foreach($user->getCurrencies(false) as $currency)
                    <div>{!! $currency->display($currency->quantity) !!}</div>
                @endforeach
            </div>
            <div class="text-right"><a href="{{ $user->url.'/bank' }}">View all...</a></div>
        </div>
    </div>
    <div class="card profile-inventory profile-assets-card">
        <div class="card-body text-center">
            <h5 class="card-title">Inventory</h5>
            <div class="profile-assets-content">
                @if(count($items))
                    <div class="row">
                        @foreach($items as $item)
                            <div class="col-md-3 col-6 profile-inventory-item">
                                <img src="{{ $item->imageUrl }}" data-toggle="tooltip" title="{{ $item->name }}" />
                            </div>
                        @endforeach
                    </div>
                @else
                    <div>No items owned.</div>
                @endif
            </div>
            <div class="text-right"><a href="{{ $user->url.'/inventory' }}">View all...</a></div>
        </div>
    </div>
</div>


<div class="text-right"><a href="{{ $user->url.'/characters' }}">View all...</a></div>
@endsection
