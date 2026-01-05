@extends('galleries.layout')

@section('gallery-title')
    {{ $submission->title }} Log
@endsection

@section('gallery-content')
    {!! breadcrumbs(['gallery' => 'gallery', $submission->gallery->displayName => 'gallery/' . $submission->gallery->id, $submission->title => 'gallery/view/' . $submission->id, 'Log Details' => 'gallery/queue/' . $submission->id]) !!}

    <h1>Log Details
        <span
            class="float-right badge badge-{{ $submission->status == 'Pending' ? 'secondary' : ($submission->status == 'Accepted' ? 'success' : 'danger') }}">{{ $submission->collaboratorApproval ? $submission->status : 'Pending Collaborator Approval' }}</span>
    </h1>

    @include('galleries._queue_submission', ['key' => 0])

    <div class="row">
        <div class="col-md">
            @if($submission->gallery->criteria)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5> Award Info <a class="small inventory-collapse-toggle collapse-toggle {{ $submission->status == 'Accepted' ? '' : 'collapsed' }}" href="#currencyForm" data-toggle="collapse">Show</a></h5>
                    </div>
                    <div class="card-body collapse {{ $submission->status == 'Accepted' ? 'show' : '' }}" id="currencyForm">
                        @if($submission->status == 'Accepted')
                            @if(!$submission->is_valued)
                                @if(Auth::user()->hasPower('manage_submissions'))
                                    {!! Form::open(['url' => 'admin/gallery/edit/'.$submission->id.'/value']) !!}
                                        <div class="h5">Contributor Rewards</div>
                                        <p>Adjust the criteria submitted and other options as needed for what the submitter, collaborators, and/or participants, should receive.</p>
                                        @if(isset($submission->data['criterion']))
                                            <h2 class="mt-5">Criteria Rewards for {!! $submission->user->displayName !!}</h2>
                                            @foreach($submission->data['criterion'] as $key => $criterionData)
                                                <div class="card p-3 mb-2">
                                                @php $criterion = \App\Models\Criteria\Criterion::where('id', $criterionData['id'])->first() @endphp
                                                <h3>{!! $criterion->displayName !!}</h3>
                                                {!! Form::hidden('criterion['.$key.'][id]', $criterionData['id']) !!}
                                                @include('criteria._minimum_requirements', ['criterion' => $criterion, 'values' => $criterionData, 'minRequirements' => $submission->gallery->criteria->where('criterion_id', $criterionData['id'])->first()->minRequirements, 'title' => 'Selections', 'limitByMinReq' => true, 'id' => $key, 'criterion_currency' => isset($criterionData['criterion_currency_id']) ? $criterionData['criterion_currency_id'] : $criterion->currency_id])
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="alert alert-secondary">This submission didn't have any criteria specified for rewards for contributors. Hitting submit will confirm this and clear it from the queue.</div>
                                        @endif

                                        {{-- TODO: Cover the commissioned participant case
                                                    -- current thought is to expose ability to add criterion to apply specifically to the commissioned person
                                                    -- expectation is that person who uploaded image would have selected the right criterion for their own rewards --}}
                                        @if ($submission->participants->count())
                                            <div class="h5 mt-3">Participant Rewards</div>
                                            @foreach($submission->participants as $key=>$participant)
                                                <div class="form-group">
                                                    {!! Form::label($participant->user->name.' ('.$participant->displayType.')') !!}:
                                                    {!! Form::number('value[participant]['.$participant->user->id.']', isset($submission->data['total']) ? ($participant->type == 'Comm' ? round(($submission->characters->count() ? round($submission->data['total'] * $submission->characters->count()) : $submission->data['total']) / ($submission->collaborators->count() ? $submission->collaborators->count() : '1')/2) : 0) : 0, ['class' => 'form-control']) !!}
                                                </div>
                                            @endforeach
                                        @endif
                                        <div class="form-group">
                                            {!! Form::checkbox('ineligible', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-onstyle' => 'danger']) !!}
                                            {!! Form::label('ineligible', 'Inelegible/Award No Currency', ['class' => 'form-check-label ml-3']) !!} {!! add_help('When on, this will mark the submission as valued, but will not award currency to any of the users listed.') !!}
                                        </div>
                                        <div class="text-right">
                                            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                                        </div>
                                    {!! Form::close() !!}
                                @else
                                    @if (isset($submission->data['staff']))
                                        <p><strong>Processed By:</strong> {!! App\Models\User\User::find($submission->data['staff'])->displayName !!}</p>
                                    @endif
                                    @if (isset($submission->data['ineligible']) && $submission->data['ineligible'] == 1)
                                        <p>This submission has been evaluated as ineligible for {{ $currency->name }} rewards.</p>
                                    @else
                                        <p>{{ $currency->name }} has been awarded for this submission.</p>
                                        <div class="row">
                                            @if (isset($submission->data['value']['submitted']))
                                                <div class="col-md-4">
                                                    {!! $submission->user->displayName !!}: {!! $currency->display($submission->data['value']['submitted'][$submission->user->id]) !!}
                                                </div>
                                            @endif
                                            @if ($submission->collaborators->count())
                                                <div class="col-md-4">
                                                    @foreach ($submission->collaborators as $collaborator)
                                                        {!! $collaborator->user->displayName !!} ({{ $collaborator->data ?? 'No extra details' }}): {!! $currency->display($submission->data['value']['collaborator'][$collaborator->user->id]) !!}
                                                        <br />
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if ($submission->participants->count())
                                                <div class="col-md-4">
                                                    @foreach ($submission->participants as $participant)
                                                        {!! $participant->user->displayName !!} ({{ $participant->displayType ?? 'No extra details' }}): {!! $currency->display($submission->data['value']['participant'][$participant->user->id]) !!}
                                                        <br />
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            @else
                                @if(isset($submission->data['staff']))<p><strong>Processed By:</strong> {!! App\Models\User\User::find($submission->data['staff'])->displayName !!}</p>@endif
                                @if(isset($submission->data['ineligible']) && $submission->data['ineligible'] == 1)
                                    <p>This submission has been evaluated as ineligible for rewards.</p>
                                @else
                                    @if(isset($totals) && count($totals) > 0)
                                        @foreach($totals as $total)
                                            <h4>{{ $total['name'] }} Criterion</h4>
                                            <div class="row">
                                            @if(!$submission->collaborators->count() || $submission->collaborators->where('user_id', $submission->user_id)->first() == null)
                                                <div class="col-md-4">
                                                {!! $submission->user->displayName !!}: {!! $total['currency']->display($total['value'] / ($collaboratorsCount ?? 1)) !!}
                                                </div>
                                            @endif
                                            @if($submission->collaborators->count())
                                                <div class="col-md-4">
                                                    <h5>Collaborators</h5>
                                                @foreach($submission->collaborators as $collaborator)
                                                    {!! $collaborator->user->displayName !!} ({{ $collaborator->data ?? 'No extra details' }}): {!! $total['currency']->display($total['value'] / ($collaboratorsCount ?? 1)) !!}
                                                <br/>
                                                @endforeach
                                                </div>
                                            @endif
                                            {{-- TODO: --}}
                                            @if($submission->participants->count())
                                                <div class="col-md-4">
                                                    <h5>Participants</h5>
                                                @foreach($submission->participants as $participant)
                                                    {!! $participant->user->displayName !!} ({{ $participant->displayType }}): {!! $total['currency']->display($total['value'] / ($collaboratorsCount ?? 1)) !!}
                                                <br/>
                                                @endforeach
                                                </div>
                                            @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <p>This submission didn't have any criteria specified for rewards.</p>
                                    @endif
                                @endif
                            @endif
                        @else
                            <p>This submission is not eligible for currency awards{{ $submission->status == 'Pending' ? ' yet-- it must be accepted first' : '' }}.</p>
                        @endif
                        @if(isset($totals) && count($totals) > 0)
                            <hr/>
                            <div id="totals">
                                @include('galleries._submission_totals', ['totals' => $totals, 'collaboratorsCount' => $collaboratorsCount])
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Staff Comments</h4> {!! Auth::user()->hasPower('staff_comments') ? '(Visible to ' . $submission->credits . ')' : '' !!}
                </div>
                <div class="card-body">
                    @if (isset($submission->parsed_staff_comments))
                        <h5>Staff Comments (Old):</h5>
                        {!! $submission->parsed_staff_comments !!}
                        <hr />
                    @endif
                    <!-- Staff-User Comments -->
                    <div class="container">
                        @comments(['model' => $submission, 'type' => 'Staff-User', 'perPage' => 5])
                    </div>
                </div>
            </div>
        </div>
        @if (Auth::user()->hasPower('manage_submissions') && $submission->collaboratorApproval)
            <div class="col-12 col-md-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>[Admin] Vote Info</h5>
                    </div>
                    <div class="card-body">
                        @if ($submission->getVoteData()['raw']->count())
                            @foreach ($submission->getVoteData(1)['raw'] as $vote)
                                <li>
                                    {!! $vote['user']->displayName !!} {{ $vote['user']->id == Auth::user()->id ? '(you)' : '' }}: <span {!! $vote['vote'] == 2 ? 'class="text-success">Accept' : 'class="text-danger">Reject' !!}</span>
                                </li>
                            @endforeach
                        @else
                            <p>No votes have been cast yet!</p>
                        @endif
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>[Admin] Staff Comments</h5> (Only visible to staff)
                    </div>
                    <div class="card-body">
                        <!-- Staff-User Comments -->
                        <div class="container">
                            @comments(['model' => $submission, 'type' => 'Staff-Staff', 'perPage' => 5, 'commentType' => 'staff'])
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

<script>
    $('input[name*=criterion]').on('change input', () => {
        const disabledInputs = $('input[name*=criterion]').filter('[disabled]');
        disabledInputs.prop('disabled', false);
        formObj = {};
        let formData = $('input[name*=criterion]').closest('form').serializeArray();
        disabledInputs.prop('disabled', true);
        formObj['_token'] = formData[0].value;
        formData.forEach((item) => formObj[item.name] = item.value);
        $(`#totals`).load('{{ url('/gallery/queue/totals/'.$submission->id) }}', formObj);
    })
</script>

@endsection
