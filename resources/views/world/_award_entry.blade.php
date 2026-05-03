<div class="row world-entry align-items-center">
    @if ($award->imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $award->imageUrl }}" data-lightbox="entry" data-title="{{ $award->displayName }}">
                <img src="{{ $award->imageUrl }}" class="world-entry-image img-fluid" /></a>
        </div>
    @endif
    <div class="{{ $award->imageUrl ? 'col-md-9' : 'col-12' }}">
        <div class="card mb-2">
            <div class="card-header d-flex flex-wrap no-gutters">
                <h1 class="col-12">
                    {!! $award->displayName !!}
                    <div class="float-md-right ml-2 small mb-0">
                        <x-admin-edit title="Award" :object="$award" />
                    </div>
                    <div class="float-md-right small mb-0">
                        @if ($award->is_character_owned)
                            <i class="fas fa-paw small" data-toggle="tooltip" title="This award can be held by characters."></i>
                        @endif
                        @if ($award->is_user_owned)
                            <i class="fas fa-user small" data-toggle="tooltip" title="This award can be held by users."></i>
                        @endif
                    </div>
                </h1>
                @if (isset($award->category) && $award->category)
                    <div class="col">
                        <strong>Category:</strong> {{ $award->category->name }}
                    </div>
                @endif
                @if (isset($award->rarity) && $award->rarity)
                    <div class="col">
                        <strong>Rarity:</strong> {!! $award->rarity->displayName !!}
                    </div>
                @endif
            </div>
            <div class="card-body">
                {!! $award->parsed_description !!}
            </div>
            @if ((isset($award->source) && $award->source) || (isset($award->data['prompts']) && $award->data['prompts']))
                <div class="card-header h5">Availability</div>
                <div class="card-body">
                    @if (isset($award->data['release']) && $award->data['release'])
                        <div><strong>Source:</strong> {!! $award->data['release'] !!}</div>
                    @endif
                    @if (isset($award->data['prompts']) && $award->data['prompts'])
                        <div class="no-gutters d-flex flex-wrap justify-content-center">
                            @foreach ($award->prompts as $prompt)
                                <a href="{{ $prompt->url }}" class="btn btn-outline-primary btn-sm mx-1">{{ $prompt->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
            @if (isset($award->credits) && $award->credits)
                <div class="card-header h5">Credits</div>
                <div class="card-body d-flex flex-wrap justify-content-center">
                    @foreach ($award->prettyCredits as $credit)
                        <span class="btn btn-outline-primary btn-sm mx-1">{!! $credit !!}</span>
                    @endforeach
                </div>
            @endif
            {{-- progression --}}
            @if (isset($staffView) && $staffView && count($award->progressions) > 0)
                <div class="card-header h5">Award Progress ({{ count($award->progressions) }}/{{ count($award->progressions) }})</div>
                <div class="card-body d-flex flex-wrap justify-content-center align-items-center">
                    <p>Note that this looks fully completed, since you are viewing it as an admin. Progression is only visible on the awards page or user inventory.</p>
                    <div class="row justify-content-center align-items-center">
                        @foreach ($award->progressions as $progression)
                            <div class="col-md-2">
                                {!! $progression->unlocked(null, true) !!}
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif (Auth::check() && count($award->progressions) > 0)
                <div class="card-header h5">
                    Award Progress ({{ $award->progressionProgress(Auth::user()) }}/{{ count($award->progressions) }})
                </div>
                {{-- get sum of award progressions that the user has unlocked --}}
                <div class="card-body text-center justify-content-center">
                    <div class="row justify-content-center align-items-center">
                        @foreach ($award->progressions as $progression)
                            <div class="col-md-2">
                                {!! $progression->unlocked(Auth::user()) !!}
                            </div>
                        @endforeach
                    </div>
                    @if ($award->progressionProgress(Auth::user()) == count($award->progressions) && $award->canClaim(Auth::user()))
                        <div class="mt-2">
                            {!! Form::open(['url' => 'awardcase/claim/' . $award->id]) !!}
                            {!! Form::submit('Claim Award', ['class' => 'btn btn-primary']) !!}
                            {!! Form::close() !!}
                        </div>
                    @elseif($award->progressionProgress(Auth::user()) == count($award->progressions) && !$award->canClaim(Auth::user()))
                        <div class="mt-2">
                            <hr class="w-50" />
                            <div class="alert alert-info">
                                You have already claimed this award!
                            </div>
                            {{-- get the user reward where the data column contains 'progression_data' in the JSON --}}
                            @php
                                $userAward = App\Models\User\UserAward::where('user_id', Auth::user()->id)
                                    ->where('award_id', $award->id)
                                    ->where('data', 'like', '%"progression_data"%')
                                    ->first();
                                $data = $userAward?->data['progression_data'] ?? [];
                            @endphp
                            @if ($userAward)
                                <p>You received this reward after gaining the following requirements:</p>
                                <div class="row text-center d-flex flex-wrap justify-content-center align-items-center">
                                    @foreach ($data as $type => $type_data)
                                        @foreach ($type_data as $id => $quantity)
                                            {{-- find the model from the type --}}
                                            @php
                                                switch ($type) {
                                                    case 'Item':
                                                        $info = App\Models\Item\Item::find($id);
                                                        break;
                                                    case 'Currency':
                                                        $info = App\Models\Currency\Currency::find($id);
                                                        break;
                                                    case 'Award':
                                                        $info = App\Models\Award\Award::find($id);
                                                        break;
                                                }
                                            @endphp

                                            <div class="col-sm-1">
                                                <img src="{{ $info->imageUrl }}" class="img-fluid" data-toggle="tooltip" title="{{ $info->name }} x{{ $quantity }}" />
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            @else
                                You received this award without using progressions!
                            @endif
                        </div>
                    @endif
                </div>
            @endif
            {{-- rewards --}}
            @if (isset($award->rewards) && count($award->rewards) > 0)
                <div class="card-header h5">Rewards</div>
                <div class="card-body text-center justify-content-center">
                    <p>Claiming this award will grant you the following rewards:</p>
                    <div class="row text-center d-flex flex-wrap justify-content-center">
                        @foreach ($award->rewards as $reward)
                            <div class="col-md-2">
                                @if ($reward->reward->imageUrl)
                                    <img src="{{ $reward->reward->imageUrl }}" class="img-fluid" data-toggle="tooltip" title="{{ $reward->reward->name }} x{{ $reward->quantity }}" />
                                    x{{ $reward->quantity }}
                                @else
                                    {!! $reward->reward->displayName !!} x{{ $reward->quantity }}
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
