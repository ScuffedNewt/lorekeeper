@extends('layouts.app')

@section('title')
    Raffle - {{ $raffle->name }}
@endsection

@section('content')
    {!! breadcrumbs(['Raffles' => 'raffles', 'Raffle: ' . $raffle->name => 'raffles/view/' . $raffle->id]) !!}
    <h1>
        Raffle: {{ $raffle->name }} {{ $raffle->is_fto ? ' (FTO / Non-Owner Only)' : '' }}</h1>
    @if ($raffle->is_active == 1)
        @if ($raffle->end_at)
            @if ($raffle->end_at < Carbon\Carbon::now())
                <div class="alert alert-danger mb-2">This raffle has closed.</div>
            @else
                <div class="alert alert-success text-center">
                    This raffle is currently open.
                    ・ Number of winners to be drawn: {{ $raffle->winner_count }}
                    @if ($raffle->ticket_cap)
                        ・ This raffle has a cap of {{ $raffle->ticket_cap }} tickets per individual.
                    @endif
                </div>
                <div class="alert alert-warning mb-2">This raffle will close {{ $raffle->end_at->format('F j, Y g:i A') }}.</div>
            @endif
        @endif
        @if ($raffle->rewards)
            <div class="alert alert-info mb-2">
                This raffle gives you rewards for entering!<br>
                <a class="card-title collapse-title" data-toggle="collapse" href="#rewards">View Rewards</a>
                <div id="rewards" class="collapse">
                    <ul>
                        @foreach ($raffle->rewards as $reward)
                            <li>{!! $reward->reward->displayName !!} x {{ $reward->quantity }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        @if ($raffle->allow_entry)
            @if (Auth::check())
                @if (!$raffle->end_at || Carbon\Carbon::now()->lt($raffle->end_at))
                    @if ($userCount > 0)
                        <div class="alert alert-info">You have already self-entered into this raffle!</div>
                    @else
                        <div class="alert alert-success">This raffle allows you to enter yourself!</div>
                        {!! Form::open(['url' => 'raffles/enter/' . $raffle->id]) !!}
                        <div class="text-right">
                            {!! Form::submit('Enter', ['class' => 'btn btn-primary']) !!}
                        </div>
                        {!! Form::close() !!}
                    @endif
                @endif
            @else
                <div class="alert alert-warning">This raffle allows you to enter yourself! Login to enter.</div>
            @endif
        @endif
    @elseif($raffle->is_active == 2)
        <div class="alert alert-danger">This raffle is closed. Rolled: {!! format_date($raffle->rolled_at) !!}</div>
        <div class="card mb-3">
            <div class="card-header h3">Winner(s)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <th class="col-xs-1 text-center" style="width: 100px;">#</th>
                        <th>User</th>
                    </thead>
                    <tbody>
                        @foreach ($raffle->tickets()->winners()->get()
        as $winner)
                            <tr>
                                <td class="text-center">{{ $winner->position }}</td>
                                <td class="text-left">{!! $winner->displayHolderName !!} @if ($winner->reroll)
                                        <span class="text-danger">(Reroll)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <h3>Tickets</h3>

    @if (Auth::check() && count($tickets))
        <?php $chance = number_format((float) (($userCount / $count) * 100), 1, '.', ''); //Change 1 to 0 if you want no decimal place. ?>
        <p class="text-center mb-0">You {{ $raffle->is_active == 2 ? 'had' : 'have' }} <strong>{{ $userCount }}</strong> out of <strong>{{ $count }} tickets</strong> in this raffle.</p>
        <p class="text-center"> That's a <strong>{{ $chance }}%</strong> chance! </p>
    @endif

    <div class="text-right">{!! $tickets->render() !!}</div>


    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-2 col-md-1 font-weight-bold">
                    <div class="logs-table-cell">#</div>
                </div>
                <div class="col-10 col-md-11 font-weight-bold">
                    <div class="logs-table-cell">User</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($tickets as $count => $ticket)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-2 col-md-1">
                            <div class="logs-table-cell">
                                {{ $page * 100 + $count + 1 }}
                                @if (Auth::check() && $ticket->user_id && $ticket->user->name == Auth::user()->name)
                                    <i class="fas fa-ticket-alt ml-2"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-10 col-md-11">
                            <div class="logs-table-cell">{!! $ticket->displayHolderName !!}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-right">{!! $tickets->render() !!}</div>

        @include('raffles._logs', ['raffle' => $raffle])

    @endsection
    @section('scripts')
        @parent
    @endsection
