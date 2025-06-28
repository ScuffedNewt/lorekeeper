@php
    if (!isset($timer)) {
        $timer = (object) ['step' => 0];
    }
@endphp

<div class="text-center">
    @if ($daily->has_image)
        <img src="{{ $daily->dailyImageUrl }}" style="max-width:100%" alt="{{ $daily->name }}" />
    @endif
    <p>{!! $daily->parsed_description !!}</p>
</div>

@if (Auth::check())
    @if ($daily->has_button_image)
        <div class="row justify-content-center mt-2">
            {!! Form::open(['url' => 'dailies/' . $daily->id, 'method' => 'post']) !!}
            {!! Form::button('<img src="' . $daily->buttonImageUrl . '" class="w-100" style="max-width:200px;" />', ['class' => 'btn', 'style' => 'background-color:transparent;', 'disabled' => $isDisabled, 'type' => 'submit']) !!}
            {!! Form::close() !!}
        </div>
    @else
        <div class="row justify-content-center mt-2">
            {!! Form::open(['url' => 'dailies/' . $daily->id, 'method' => 'post']) !!}
            {!! Form::submit('Collect!', ['class' => 'btn btn-primary', 'disabled' => $isDisabled]) !!}
            {!! Form::close() !!}
        </div>
    @endif
    <div class="text-center">
        <hr>
        <small>
            @if ($daily->daily_timeframe == 'lifetime')
                You will be able to collect rewards once.
            @else
                You will be able to collect rewards {!! $daily->daily_timeframe !!}.
            @endif
            @if (Auth::check() && isset($cooldown))
                You can collect rewards {!! pretty_date($cooldown) !!}!
            @endif
        </small>
    </div>
@else
    <div class="row mt-2 mb-2 justify-content-center">
        <div class="alert alert-danger" role="alert">
            You must be logged in to collect {{ __('dailies.dailies') }}!
        </div>
    </div>
@endif

@if ($daily->prize_display != 'none')
    <div class="card mt-5">
        <div class="card-header">
            <h4 class="m-0 align-items-center">
                Progress ({{ $timer->step }}/{{ $daily->maxStep }}) {!! add_help($daily->is_streak ? 'Progress will reset if you miss collecting your reward in the given timeframe.' : 'Progress is safe even if you miss collecting your reward in the given timeframe.') !!}
            </h4>
        </div>

        <div class="card-body row p-0 m-auto w-100">
            @foreach ($daily->rewards()->get()->groupBy('step') as $step => $rewards)
                @if ($step > 0)
                    <div class="col w-100 text-center justify-content-center border p-0">
                        <h5 class="p-1 m-0 w-100 {{ $step <= $timer->step ? 'btn-primary' : 'btn-dark' }} mb-2">
                            @if ($step > $timer->step)
                                <i class="fa fa-lock"></i>
                            @else
                                <i class="fa fa-unlock"></i>
                            @endif
                            #{{ $step }}
                        </h5>
                        @if ($daily->prize_display == 'all' || $step <= $timer->step)
                            <div class="row text-center justify-content-center">
                                @foreach ($rewards as $reward)
                                    <div class="col-md-6 col-sm-12">
                                        @if ($reward->rewardImage)
                                            <div class="row justify-content-center">
                                                <img src="{{ $reward->rewardImage }}" alt="{{ $reward->reward->name }}" style="max-width:75px;width:100%;" />
                                            </div>
                                        @endif
                                        <div class="row justify-content-center">
                                            {{ $reward->quantity }} {!! $reward->reward->displayName !!}
                                        </div>
                                    </div>
                                @endforeach
                                @if ($step <= $timer->step)
                                    <div class="col-12">
                                        <div class="alert alert-success mb-0 m-2" role="alert">
                                            Rewards collected!
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning mb-0" role="alert">
                                Rewards are hidden until collected.
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif
