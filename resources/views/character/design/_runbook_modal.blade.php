@php
    $runbooks = \App\Models\Runbook::where('type', 'Design Update')->get();
@endphp

<div class="card runbook-slide-card" id="runbookCard" style="width: 40vw; position: fixed; top: 20px; right: 20px; z-index: 1050;">
    <button id="toggleBtn" class="runbook-toggle-btn">&gt;&gt;</button>
    <div class="card-body">
        <h5 class="card-title">
            Design Update Runbooks
        </h5>
        <div class="card-text">
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

<script>
    const card = $('#runbookCard');
    const btn = $('#toggleBtn');

    btn.on('click', () => {
        card.toggleClass('hidden');
        btn.html(card.hasClass('hidden') ? '&lt;&lt;' : '&gt;&gt;');
    });
</script>