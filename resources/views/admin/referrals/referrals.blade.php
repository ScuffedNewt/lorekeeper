@extends('admin.layout')

@section('admin-title') Referrals @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Referrals' => 'admin/data/referrals']) !!}

<h1>Referrals</h1>

<p>This is a list of referral counts that have rewards granted for them.</p>

<div class="text-right mb-3">
    <a class="btn btn-primary" href="{{ url('admin/data/referrals/create') }}"><i class="fas fa-plus"></i> Create New Referral</a>
</div>

@if(!isset($referrals) || !count($referrals))
    <p>No referrals found.</p>
@else
    {!! $referrals->render() !!}

    <div class="row ml-md-2">
      <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
        <div class="col-4 col-md-1 font-weight-bold">Active</div>
        <div class="col-7 col-md-10 font-weight-bold">Referral Count</div>
      </div>
      @foreach($referrals as $referral)
      <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
        <div class="col-4 col-md-1">
          {!! $referral->is_active ? '<i class="text-success fas fa-check"></i>' : '' !!}
        </div>
        <div class="col-7 col-md-10 text-truncate">
          {{ $referral->referral_count }}
        </div>
        <div class="col-3 col-md-1 text-right">
          <a href="{{ url('admin/data/referrals/edit/'.$referral->id) }}"  class="btn btn-primary py-0 px-2">Edit</a>
        </div>
      </div>
      @endforeach
    </div>

    {!! $referrals->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $referrals->total() }} result{{ $referrals->total() == 1 ? '' : 's' }} found.</div>
@endif

@endsection

@section('scripts')
@parent
@endsection
