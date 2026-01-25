@extends('world.layout')

@section('title')
    Levels
@endsection

@section('content')
    {!! breadcrumbs(['Encyclopedia' => 'world', 'Levels' => 'levels']) !!}

    <div class="card mb-3">
        <div class="card-body">
            <div class="row world-entry">
                <h1 class="ml-3">Level {{ $level->level }}</h1>
            </div>
            {!! $level->description !!}
            <div class="world-entry-text">
                <div class="row">
                    <div class="col-6">
                        @include('widgets._limits', ['object' => $level])
                    </div>
                    <div class="col-6">
                        <h4>Rewards</h4>
                        @if ($level->rewards->count())
                            <p>You will receive the following rewards when you reach this level:</p>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th width="70%">Requires</th>
                                        <th width="30%">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($level->rewards as $limit)
                                        <tr>
                                            <td>{!! $limit->reward ? $limit->reward->displayName : $limit->rewardable_type !!}</td>
                                            <td>{{ $limit->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>No rewards.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    @endsection
