<div class="card">
    <div class="card-header" href="#runbook-{{ $runbook->id }}" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="runbook-{{ $runbook->id }}" style="background-color: transparent;">
        {{ $runbook->title }}
    </div>
    <div class="collapse" id="runbook-{{ $runbook->id }}">
        <div class="card-body">
            {!! parseRunbooks($runbook->text) !!}
        </div>
    </div>
</div>