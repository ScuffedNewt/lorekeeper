@extends('admin.layout')

@section('admin-title')
    Mod Mail
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Mod Mail' => 'admin/mail']) !!}

    <div class="float-right">
        <a href="{{ url('admin/mail/create') }}" class="btn btn-primary">Send Mail</a>
    </div>

    <h1>Mod Mail</h1>

    <p>Mod Mail can be sent to a user anonymously by staff in order to issue strikes, warnings, information etc. <br> Mail can be used to automatically ban users after a set number of strikes (see the setting "user_strike_count" in <a
            href="{{ url('admin/settings') }}">Site Settings</a>).</p>

    @if (!count($mails))
        <p>No mail found.</p>
    @else
        {!! $mails->render() !!}

        <div class="row ml-md-2">
            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
                <div class="col-12 col-md-2 font-weight-bold">Subject</div>
                <div class="col-6 col-md-3 font-weight-bold">To</div>
                <div class="col-6 col-md-4 font-weight-bold">Sent</div>
                <div class="col-6 col-md-2 font-weight-bold">Seen</div>
                <div class="col-12 col-md-1 font-weight-bold">Details</div>
            </div>

            @foreach ($mails as $mail)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                    <div class="col-12 col-md-2">
                        {{ $mail->subject }}
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="ubt-texthide">{!! $mail->user->displayName !!}</span>
                    </div>
                    <div class="col-6 col-md-4">{!! pretty_date($mail->created_at) !!} to {!! $mail->staff->displayName !!}</div>
                    <div class="col-6 col-md-2">{!! $mail->seen ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}</div>
                    <div class="col-12 col-md-1">
                        <a href="{{ url('admin/mail/view') }}/{{ $mail->id }}" class="btn btn-primary btn-sm py-0 px-1">Details</a>
                    </div>
                </div>
            @endforeach
        </div>

        {!! $mails->render() !!}
        <div class="text-center mt-4 small text-muted">{{ $mails->total() }} result{{ $mails->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection
