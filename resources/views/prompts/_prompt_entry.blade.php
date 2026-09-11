<div class="row world-entry">
    @if ($prompt->has_image)
        <div class="col-md-3 world-entry-image"><a href="{{ $prompt->imageUrl }}" data-lightbox="entry" data-title="{{ $prompt->name }}"><img src="{{ $prompt->imageUrl }}" class="world-entry-image" alt="{{ $prompt->name }}" /></a></div>
    @endif
    <div class="{{ $prompt->has_image ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Prompt" :object="$prompt" />
        <div class="mb-3">
            @if (isset($isPage))
                <h1 class="mb-0">{!! $prompt->name !!} <a href="{{ $prompt->idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a></h1>
            @else
                <h2 class="mb-0"><a href="{{ $prompt->idUrl }}">{!! $prompt->name !!}</a></h2>
            @endif
            @if ($prompt->prompt_category_id)
                <div><strong>Category: </strong>{!! $prompt->category->displayName !!}</div>
            @endif
            @if ($prompt->start_at && $prompt->start_at->isFuture())
                <div><strong>Starts: </strong>{!! format_date($prompt->start_at) !!} ({{ $prompt->start_at->diffForHumans() }})</div>
            @endif
            @if ($prompt->end_at)
                <div><strong>Ends: </strong>{!! format_date($prompt->end_at) !!} ({{ $prompt->end_at->diffForHumans() }})</div>
            @endif
        </div>
        <div class="world-entry-text">
            <p>{{ $prompt->summary }}</p>
            <h3 class="mb-3"><a data-toggle="collapse" href="#prompt-{{ $prompt->id }}" @if (isset($isPage)) aria-expanded="true" @endif>Details <i class="fas fa-angle-down"></i></a></h3>
            <div class="collapse @if (isset($isPage)) show @endif" id="prompt-{{ $prompt->id }}">
                @if ($prompt->parsed_description)
                    {!! $prompt->parsed_description !!}
                @else
                    <p>No further details.</p>
                @endif
                @if ($prompt->hide_submissions == 1 && isset($prompt->end_at) && $prompt->end_at > Carbon\Carbon::now())
                    <p class="text-info">Submissions to this prompt are hidden until this prompt ends.</p>
                @elseif($prompt->hide_submissions == 2)
                    <p class="text-info">Submissions to this prompt are hidden.</p>
                @endif
            </div>
            <h3>Rewards</h3>
            @if (!count($prompt->rewards))
                No rewards.
            @else
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th width="70%">Reward</th>
                            <th width="30%">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prompt->rewards as $reward)
                            <tr>
                                <td>
                                    {!! $reward->rewardable_recipient == 'User' ? '<i class="fas fa-user" data-toggle="tooltip" title="User Reward"></i>' : '<i class="fas fa-paw" data-toggle="tooltip" title="Character Reward"></i>' !!}
                                    {!! $reward->reward ? $reward->reward->displayName : $reward->rewardable_type !!}
                                </td>
                                <td>{{ $reward->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            @if ($prompt->children->count())
                <h4 class="mt-2">Unlocks The Following Prompts:</h4>
                <div class="row">
                    @foreach ($prompt->children as $children)
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        {!! $children->displayname !!}
                                    </h5>
                                    <p class="card-text">
                                        {!! $children->summary !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @if ($prompt->limits)
                <hr />
                @include('widgets._limits', [
                    'object' => $prompt,
                    'hideUnlock' => true,
                ])
            @endif
        </div>
        <div class="text-right {{ $prompt->limit ? 'text-danger' : '' }}">
            <p class="mb-1 mt-2">
                {{ $prompt->limit ? 'You can submit this prompt ' . $prompt->limit . ' time(s)' : 'You can submit this prompt an unlimited number of times' }}
                {{ $prompt->limit_period ? ' per ' . strtolower($prompt->limit_period) : '' }}
                {{ $prompt->limit_character ? ' per character' : '' }}.
            </p>
        </div>
        <div class="text-right">
            @if ($prompt->parent_id)
                @if ($prompt->parent->getSubmissionCount(Auth::user() ?? null) < $prompt->parent_quantity)
                    <p class="text-danger">You have not unlocked this prompt yet. You must complete {!! $prompt->parent->displayName !!} {{ $prompt->parent_quantity }} {{ $prompt->parent_quantity > 1 ? 'times' : 'time' }}.</p>
                @else
                    <p class="text-success">You have unlocked this prompt by completing {!! $prompt->parent->displayName !!} {{ $prompt->parent_quantity }} {{ $prompt->parent_quantity > 1 ? 'times' : 'time' }}.</p>
                @endif
            @endif
            @if ($prompt->end_at && $prompt->end_at->isPast())
                <span class="text-secondary">This prompt has ended.</span>
            @elseif($prompt->start_at && $prompt->start_at->isFuture())
                <span class="text-secondary">This prompt is not open for submissions yet.</span>
            @else
                <a href="{{ url('submissions/new?prompt_id=' . $prompt->id) }}" class="btn btn-primary">Submit Prompt</a>
            @endunless
    </div>
</div>
</div>
