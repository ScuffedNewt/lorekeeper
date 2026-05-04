@extends('home.layout')

@section('home-title')
    Mod Mail
@endsection

@section('home-content')

    {!! breadcrumbs(['Inbox' => 'inbox']) !!}

    <h1>
        Inbox
    </h1>

    <div class="text-right">
        <a href="{{ url('inbox/new') }}" class="btn btn-success">New Message</a>
    </div>

    <ul class="nav nav-tabs mb-3" id="inboxType" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="inbox-tab" data-toggle="tab" href="#inbox" role="tab">Inbox</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="outbox-tab" data-toggle="tab" href="#outbox" role="tab">Outbox</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="mod-mail-tab" data-toggle="tab" href="#modMail" role="tab">Mod Mail</a>
        </li>
    </ul>
    <div class="tab-content" id="inboxTypeContent">
        <div class="tab-pane fade show active" id="inbox" role="tabpanel">
            @if (count($inbox))
                <div class="row ml-md-2">
                    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
                        <div class="col-12 col-md-2 font-weight-bold">Subject</div>
                        <div class="col-6 col-md-3 font-weight-bold">Message</div>
                        <div class="col-6 col-md-4 font-weight-bold">From</div>
                        <div class="col-6 col-md-2 font-weight-bold">Seen</div>
                        <div class="col-12 col-md-1 font-weight-bold">Details</div>
                    </div>

                    @foreach ($inbox as $mail)
                        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                            <div class="col-12 col-md-2">{!! $mail->displayName !!}</div>
                            <div class="col-6 col-md-3">
                                <span class="ubt-texthide">{{ Illuminate\Support\Str::limit($mail->message, 50, $end = '...') }}</span>
                            </div>
                            <div class="col-6 col-md-4">{!! $mail->sender->displayName !!} {!! pretty_date($mail->created_at) !!}</div>
                            <div class="col-6 col-md-2">{!! $mail->seen ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}</div>
                            <div class="col-12 col-md-1">
                                <a href="{{ $mail->viewUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-4 small text-muted">{{ $inbox->count() }} result{{ $inbox->count() == 1 ? '' : 's' }} found.</div>
            @else
                <p>Your inbox is empty.</p>
            @endif
        </div>
        <div class="tab-pane fade" id="outbox" role="tabpanel">
            @if (count($outbox))
                <div class="row ml-md-2">
                    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
                        <div class="col-12 col-md-2 font-weight-bold">Subject</div>
                        <div class="col-6 col-md-3 font-weight-bold">Message</div>
                        <div class="col-6 col-md-4 font-weight-bold">To</div>
                        <div class="col-6 col-md-2 font-weight-bold">Seen</div>
                        <div class="col-12 col-md-1 font-weight-bold">Details</div>
                    </div>

                    @foreach ($outbox as $mail)
                        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                            <div class="col-12 col-md-2">{!! $mail->displayName !!}</div>
                            <div class="col-6 col-md-3">
                                <span class="ubt-texthide">{{ Illuminate\Support\Str::limit($mail->message, 50, $end = '...') }}</span>
                            </div>
                            <div class="col-6 col-md-4">{!! $mail->recipient->displayName !!} {!! pretty_date($mail->created_at) !!}</div>
                            <div class="col-6 col-md-2">{!! $mail->seen ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}</div>
                            <div class="col-12 col-md-1">
                                <a href="{{ $mail->viewUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-4 small text-muted">{{ $outbox->count() }} result{{ $outbox->count() == 1 ? '' : 's' }} found.</div>
            @else
                <p>Your outbox is empty.</p>
            @endif
        </div>
        <div class="tab-pane fade" id="modMail" role="tabpanel">
            <p class="alert alert-info">This mail is anonymously sent messages from moderators. It cannot be responded to.</p>
            @if (count($mails))
                <div class="row ml-md-2">
                    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
                        <div class="col-12 col-md-2 font-weight-bold">Subject</div>
                        <div class="col-6 col-md-3 font-weight-bold">Message</div>
                        <div class="col-6 col-md-4 font-weight-bold">Sent</div>
                        <div class="col-6 col-md-2 font-weight-bold">Seen</div>
                        <div class="col-12 col-md-1 font-weight-bold">Details</div>
                    </div>

                    @foreach ($mails as $mail)
                        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                            <div class="col-12 col-md-2">{{ $mail->subject }}</div>
                            <div class="col-6 col-md-3">
                                <span class="ubt-texthide">{{ Illuminate\Support\Str::limit($mail->message, 50, $end = '...') }}</span>
                            </div>
                            <div class="col-6 col-md-4">{!! pretty_date($mail->created_at) !!}</div>
                            <div class="col-6 col-md-2">{!! $mail->seen ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}</div>
                            <div class="col-12 col-md-1">
                                <a href="{{ $mail->viewUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-4 small text-muted">{{ $mails->count() }} result{{ $mails->count() == 1 ? '' : 's' }} found.</div>
            @else
                <p>No mod mail found.</p>
            @endif
        </div>
    </div>

    <script>
        $(function() {
            var hash = window.location.hash;
            hash && $('ul.nav a[href="' + hash + '"]').tab('show');

            $('.nav-tabs a').click(function(e) {
                $(this).tab('show');
                var scrollmem = $('body').scrollTop();
                window.location.hash = this.hash;
                $('html,body').scrollTop(scrollmem);
            });
        });
    </script>

@endsection
