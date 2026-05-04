@extends('home.layout')

@section('home-title')
    Mod Mail (#{{ $mail->id }})
@endsection

@section('home-content')
    {!! breadcrumbs(['Mod Mail' => 'mail', 'Mod Mail (#' . $mail->id . ')' => $mail->viewUrl]) !!}

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6">
                    <h3>Mail #{{ $mail->id }} - {{ $mail->subject }}</h3>
                </div>
                <div class="col-6 text-right">
                    <h5>Sent {!! pretty_date($mail->created_at) !!}</h5>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="card-text">
                {!! $mail->message !!}
            </div>
            @if ($mail->issue_strike)
                <hr>
                <div class="card-text text-danger float-right">
                    <h5>Strike{{ $mail->strike_count > 1 ? 's' : '' }} issued</h5>
                    <strong>Amount:</strong> {{ $mail->strike_count }}
                </div>
            @endif
        </div>
    </div>
@endsection
