@extends('admin.layout')

@section('admin-title') Referrals @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Referrals' => 'admin/data/referrals', ($referral->id ? 'Edit' : 'Create').' Referral' => $referral->id ? 'admin/data/referrals/edit/'.$referral->id : 'admin/data/referrals/create']) !!}

<h1>{{ $referral->id ? 'Edit' : 'Create' }} Referral
    @if($referral->id)
        <a href="#" class="btn btn-danger float-right delete-referral-button">Delete Referral</a>
    @endif
</h1>

{!! Form::open(['url' => $referral->id ? 'admin/data/referrals/edit/'.$referral->id : 'admin/data/referrals/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="row align-items-end">
    <div class="col-md-8">
        <div class="form-group">
            {!! Form::label('Number of Referrals') !!} {!! add_help('This is the number of times someone has referred another player to the site.') !!}
            {!! Form::number('referral_count', $referral->referral_count ?? 1, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md">
        <div class="form-group">
            {!! Form::checkbox('on_every', 1, $referral->id ? $referral->on_every : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('on_every', 'On Every x Referrals', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Rewards on a cycle for every x number of referrals as filled in under Referral Count.') !!}
        </div>
    </div>
</div>


<div class="form-group">
    {!! Form::checkbox('is_active', 1, $referral->id ? $referral->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Referrals that are not active will not be rewarded.') !!}
</div>

<h3>Rewards</h3>
<p>Rewards are credited to the user specified in the "referred by" field on registration.</p>
@include('widgets._loot_select', ['loots' => $referral->data, 'showLootTables' => true, 'showRaffles' => true])

<div class="text-right">
    {!! Form::submit($referral->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@include('widgets._loot_select_row', ['items' => $items, 'currencies' => $currencies, 'tables' => $tables, 'raffles' => $raffles, 'showLootTables' => true, 'showRaffles' => true])

@endsection

@section('scripts')
@parent
@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
<script>
$( document ).ready(function() {    
    $('.delete-referral-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/referrals/delete') }}/{{ $referral->id }}", 'Delete Referral');
    });
});
    
</script>
@endsection