@extends('home.layout')

@section('home-title') Transfer Requests @endsection

@section('home-content')

  {!! breadcrumbs(['Transfer Requests' => 'transfer-requests']) !!}


<h1>
    Transfer Request Queue
</h1>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
      <a class="nav-link {{ !Request::get('type') || Request::get('type') == 'pending' ? 'active' : '' }}" href="transfer-requests">Pending</a>
  </li>
  <li class="nav-item">
      <a class="nav-link {{ Request::get('type') == 'accepted' ? 'active' : '' }}" href="transfer-requests?type=accepted">Approved</a>
  </li>
  <li class="nav-item">
      <a class="nav-link {{ Request::get('type') == 'rejected' ? 'active' : '' }}" href="transfer-requests?type=rejected">Rejected</a>
  </li>
</ul>

{!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select('sort', [
                'newest'         => 'Newest First',
                'oldest'         => 'Oldest First',
            ], Request::get('sort') ? : 'oldest', ['class' => 'form-control']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
{!! Form::close() !!}

{!! $transfers->render() !!}

<div class="row ml-md-2">
  <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
    <div class="col-6 col-md-3 font-weight-bold">User</div>
    <div class="col-6 col-md-3 font-weight-bold">Recipient</div>
    <div class="col-6 col-md-3 font-weight-bold">Submitted</div>
    <div class="col-6 col-md-1 font-weight-bold">Status</div>
  </div>

  @foreach($transfers as $transfer)
    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
      <div class="col-6 col-md-3">{!! $transfer->user->displayName !!}</div>
      <div class="col-6 col-md-3">{!! $transfer->recipient->displayName !!}</div>
      <div class="col-6 col-md-3">{!! pretty_date($transfer->created_at) !!}</div>
      <div class="col-3 col-md-1">
        <span class="btn btn-{{ $transfer->status == 'Pending' ? 'secondary' : ($transfer->status == 'Accepted' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $transfer->status }}</span>
      </div>
      <div class="col-3 col-md-1"><a href="{{ $transfer->viewUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a></div>
    </div>
  @endforeach

</div>

{!! $transfers->render() !!}
<div class="text-center mt-4 small text-muted">{{ $transfers->total() }} result{{ $transfers->total() == 1 ? '' : 's' }} found.</div>


@endsection
