@php
    $runbooks = \App\Models\Runbook::where('type', 'Prompt Submission')->get();
@endphp

<div class="card runbook-slide-card" id="runbookCard" style="width: 40vw; position: fixed; top: 20px; right: 20px; z-index: 1050;">
    <button id="toggleBtn" type="button" class="runbook-toggle-btn">
        &raquo;&raquo;
    </button>
    <div class="card-body" style="overflow-y: scroll; max-height: 95vh;">
        <h5 class="card-title">
            Prompt Submission Runbooks
        </h5>
        <div class="card-text">
            <div id="runbookSearchWrapper" class="input-group input-group-sm">
                <input id="runbookSearch" type="text" class="form-control" placeholder="Search runbooks…">
                <div class="input-group-append">
                    <button id="runbookClear" class="btn btn-outline-secondary" type="button" title="Clear">&times;</button>
                </div>
            </div>
            @foreach ($runbooks as $runbook)
                <div class="card {{ $loop->last ? '' : 'mb-3' }}">
                    <div class="card-header" href="#runbook-{{ $runbook->id }}" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="runbook-{{ $runbook->id }}">
                        {{ $runbook->title }}
                    </div>
                    <div class="collapse" id="runbook-{{ $runbook->id }}">
                        <div class="card-body mb-0">
                            {!! parseRunbooks($runbook->text) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@include('runbooks._runbook_js')
