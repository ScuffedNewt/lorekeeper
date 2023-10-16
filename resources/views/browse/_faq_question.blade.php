<div class="card p-4 mb-3">
    <h4 class="card-title">
        @if (!$faq->is_visible) <i class="fas fa-eye-slash mr-1"></i> @endif
        {{ $faq->question }}
        @if ($faq->tags)
            @foreach(json_decode($faq->tags) as $tag)
                <div class="badge badge-primary mx-1" style="float: right;">{{ $tag }}</div>
            @endforeach
        @endif
    </h4>
    <div class="card-text">
        {!! $faq->parsed_answer !!}
    </div>
</div>
