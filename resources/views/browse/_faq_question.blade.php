<div class="card p-4 mb-3">
    <h4 class="card-title">
        @if (!$faq->is_visible)
            <i class="fas fa-eye-slash mr-1"></i>
        @endif
        <a href="#" data-id="{{ $faq->id }}" class="faq-link">{{ $faq->question }}</a>
        @if ($faq->tags)
            @php
                $question_tags = $faq->tags;
                ksort($question_tags);
            @endphp
            @foreach ($question_tags as $tag)
                <div class="badge badge-primary mx-1" style="float: right;">{{ $tag }}</div>
            @endforeach
        @endif
    </h4>
    <div class="card-text">
        {!! $faq->parsed_answer !!}
        <div class="float-right">
            <b>Last Updated:</b> {!! pretty_date($faq->updated_at) !!}
        </div>
    </div>
</div>
