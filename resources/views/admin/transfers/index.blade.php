@extends('admin.layout')

@section('admin-title') Transfer Request Queue @endsection

@section('admin-content')

  {!! breadcrumbs(['Admin Panel' => 'admin', 'Transfer Request Queue' => 'admin/transfer-requests/pending']) !!}


<h1>
    Transfer Request Queue
</h1>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <a class="nav-link {{ set_active('admin/transfer-requests/pending*') }} {{ set_active('admin/transfer-requests') }}" href="{{ url('admin/transfer-requests/pending') }}">Pending</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ set_active('admin/transfer-requests/accepted*') }}" href="{{ url('admin/transfer-requests/accepted') }}">Approved</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ set_active('admin/transfer-requests/rejected*') }}" href="{{ url('admin/transfer-requests/rejected') }}">Rejected</a>
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
        <span class="btn btn-{{ $transfer->status == 'Pending' ? 'secondary' : ($transfer->status == 'Approved' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $transfer->status }}</span>
      </div>
      <div class="col-3 col-md-1"><a href="{{ $transfer->adminUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a></div>
    </div>
  @endforeach

</div>

{!! $transfers->render() !!}
<div class="text-center mt-4 small text-muted">{{ $transfers->total() }} result{{ $transfers->total() == 1 ? '' : 's' }} found.</div>


@endsection
