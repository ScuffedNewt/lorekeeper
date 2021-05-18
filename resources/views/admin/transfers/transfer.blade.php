@extends('home.layout')

@section('home-title') Transfer Request (#{{ $transfer->id }}) @endsection

@section('home-content')

{!! breadcrumbs(['Admin Panel' => 'admin', 'Transfer Request Queue' => 'admin/transfer-requests/pending', 'Request (#' . $transfer->id . ')' => $transfer->viewUrl]) !!}


@if($transfer->status == 'Pending')

    <h1>
        Transfer Request (#{{ $transfer->id }})
        <span class="float-right badge badge-{{ $transfer->status == 'Pending' ? 'secondary' : ($transfer->status == 'Accepted' ? 'success' : 'danger') }}">{{ $transfer->status }}</span>
    </h1>

    <div class="mb-1">
        <div class="row">
            <div class="col-md-2 col-4"><h4>User</h4></div>
            <div class="col-md-10 col-8">{!! $transfer->user->displayName !!}</div>
        </div>
        <div class="row">
            <div class="col-md-2 col-4"><h4>Recipient</h4></div>
            <div class="col-md-10 col-8">{!! $transfer->recipient->displayname !!}</div>
        </div>
        <div class="row">
            <div class="col-md-2 col-4"><h4>Submitted</h4></div>
            <div class="col-md-10 col-8">{!! format_date($transfer->created_at) !!} ({{ $transfer->created_at->diffForHumans() }})</div>
        </div>
    </div>

    <div class="row col-12">
        <div class="col-6">
            @if($object->AssetType == 'user_items')
                <h2>Item</h2>
                <div class="card mb-3">
                    <div class="card-body">
                        @if($object->item->imageUrl)
                            <img src="{{ $object->item->imageUrl }}" data-toggle="tooltip" title="{{ $object->item->name }}">
                        @else
                            {!! $object->item->displayName !!}
                        @endif
                        x {{ $quantity }}
                    </div>
                </div>
            @else
            <h2>Currency</h2>
                <div class="card mb-3">
                    <div class="card-body">
                        @if($object->currency->imageUrl)
                            <img src="{{ $object->currency->imageUrl }}" data-toggle="tooltip" title="{{ $object->currency->name }}">
                        @else
                            {!! $object->currency->displayName !!}
                        @endif
                        x {{ $quantity }}
                    </div>
                </div>
            @endif
        </div>
        <div class="col-6">
            <h2>Reason for Transfer</h2>
            <div class="card mb-3"><div class="card-body">{!! nl2br(htmlentities($transfer->reason)) !!}</div></div>
            @if(Auth::check() && $transfer->staff_comments && ($transfer->user_id == Auth::user()->id || Auth::user()->isStaff))
                <h2>Staff Comments ({!! $transfer->staff->displayName !!})</h2>
                <div class="card mb-3"><div class="card-body">
                    {!! $transfer->staff_comments !!}
                </div></div>
            @endif
        </div>
    </div>

    {!! Form::open(['url' => url()->current(), 'id' => 'transferForm']) !!}

		<div class="form-group">
            {!! Form::label('staff_comments', 'Staff Comments (Optional)') !!}
			{!! Form::textarea('staff_comments', $transfer->staffComments, ['class' => 'form-control wysiwyg']) !!}
        </div>

        <div class="text-right">
            <a href="#" class="btn btn-danger mr-2" id="rejectionButton">Reject</a>
            <a href="#" class="btn btn-success" id="approvalButton">Approve</a>
        </div>

    {!! Form::close() !!}


    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content hide" id="approvalContent">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm Approval</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will approve the transfer and distribute the objects to the recipient.</p>
                    <div class="text-right">
                        <a href="#" id="approvalSubmit" class="btn btn-success">Approve</a>
                    </div>
                </div>
            </div>
            <div class="modal-content hide" id="rejectionContent">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm Rejection</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will reject the transfer.</p>
                    <div class="text-right">
                        <a href="#" id="rejectionSubmit" class="btn btn-danger">Reject</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-danger">This transfer has already been processed.</div>
    @include('home._transfer_content', ['transfer' => $transfer])
@endif

@endsection

@section('scripts')
@parent
@if($transfer->status == 'Pending')

    <script>

        $(document).ready(function() {
            var $confirmationModal = $('#confirmationModal');
            var $transferForm = $('#transferForm');

            var $approvalButton = $('#approvalButton');
            var $approvalContent = $('#approvalContent');
            var $approvalSubmit = $('#approvalSubmit');

            var $rejectionButton = $('#rejectionButton');
            var $rejectionContent = $('#rejectionContent');
            var $rejectionSubmit = $('#rejectionSubmit');

            $approvalButton.on('click', function(e) {
                e.preventDefault();
                $approvalContent.removeClass('hide');
                $rejectionContent.addClass('hide');
                $confirmationModal.modal('show');
            });

            $rejectionButton.on('click', function(e) {
                e.preventDefault();
                $rejectionContent.removeClass('hide');
                $approvalContent.addClass('hide');
                $confirmationModal.modal('show');
            });

            $approvalSubmit.on('click', function(e) {
                e.preventDefault();
                $transferForm.attr('action', '{{ url()->current() }}/approve');
                $transferForm.submit();
            });

            $rejectionSubmit.on('click', function(e) {
                e.preventDefault();
                $transferForm.attr('action', '{{ url()->current() }}/reject');
                $transferForm.submit();
            });
        });

    </script>
@endif
@endsection
