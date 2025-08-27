@extends('layouts.app')

@section('title')
    Raffles
@endsection

@section('content')
    {!! breadcrumbs(['Raffles' => 'raffles']) !!}
    <h1>Raffles</h1>
    <p>Click on the name of a raffle to view the tickets, and in the case of completed raffles, the winners. Raffles in a group with a title will be rolled consecutively starting from the top, and will not draw duplicate winners.</p>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a href="{{ url()->current() }}" class="nav-link {{ Request::get('view') ? '' : 'active' }}">Current Raffles</a></li>
        <li class="nav-item"><a href="{{ url()->current() }}?view=completed" class="nav-link {{ Request::get('view') == 'completed' ? 'active' : '' }}">Completed Raffles</a></li>
    </ul>

    @if (count($raffles))
        @foreach ($raffles as $key => $raffle)
            <div class="card mb-3">
                @if ($key != 'Ungrouped')
                    <div class="card-header">
                        <h3 class="d-inline mb-0">
                            {{ $key }}
                        </h3>
                    </div>
                @endif

                <ul class="list-group list-group-flush">
                    @foreach ($raffle as $r)
                        <li class="list-group-item">
                            <x-admin-edit title="Raffle" :object="$r" />
                            <a href="{{ url('raffles/view/' . $r->id) }}">
                                {{ $r->name }} {{ $r->is_fto ? ' (FTO / Non-Owner Only)' : '' }}
                            </a>
                            {!! $r->rolled_at ? '<span class="text-muted small">(Rolled ' . pretty_date($r->rolled_at) . ')</span>' : '' !!}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    @else
        <p>No raffles found.</p>
    @endif
@endsection

@section('scripts')
    @parent
@endsection
