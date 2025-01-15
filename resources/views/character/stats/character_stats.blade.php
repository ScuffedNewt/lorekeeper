@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s Stat Profile
@endsection

@section('profile-content')
    {!! breadcrumbs([
        $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
        $character->fullName => $character->url,
        'Stats' => $character->url . '/stats',
    ]) !!}

    <h1>
        <a href="{{ $character->url }}">{!! $character->fullName !!}'s</a> Character Stat Profile
    </h1>
    <p>
        Here you can view {!! $character->fullName !!}'s stats and level information.
    </p>

    @include('widgets._level_info', ['level' => $character->level])

    <div class="card mb-3">
        <div class="card-header h2">
            Stat Information
            <span class="badge badge-dark text-white mx-1 float-right" data-toggle="tooltip" title="Current Stat Points">
                Available Stat Points: {{ $character->level->current_points }}
            </span>
        </div>
        <div class="card-body">
            @foreach ($character->stats->chunk(4) as $chunk)
                <div class="row justify-content-center no-gutters">
                    @foreach ($chunk as $stat)
                        <div class="col-md-2 p-1 m-2 rounded p-2 stat-entry" style="background-color: {{ $stat->stat->colour }};" data-id="{{ $stat->id }}">
                            <h5 class="text-center">
                                <i class="{!! $stat->icon ? $stat->icon . ' mr-2' : '' !!} "></i> {{ $stat->stat->name }}
                                (lvl {{ $stat->stat_level }})
                            </h5>
                            <div class="text-center">
                                <p>
                                    <b>Stat Value:</b>
                                    <u>
                                        <span data-toggle="tooltip" title="Base Stat: {{ $stat->count }}">
                                            {{ $character->totalStatCount($stat->stat->id) . ' (+ ' . $character->totalStatCount($stat->stat->id) - $stat->count . ')' }}
                                            {!! $character->totalStatCount($stat->stat->id) - $stat->count > 0 ? add_help('This stat has gained extra points through equipment.') : '' !!}
                                        </span>
                                    </u>
                                    <br />
                                    <b>Current Value:</b>
                                    <u>
                                        {{ $character->currentStatCount($stat->stat->id) }}
                                        {!! add_help('This is the current value of the stat. This can differ due to debuffs, damage taken, etc.') !!}
                                    </u>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
            <hr class="my-3">
            <h4>Current Equipment</h4>
            <div class="row justify-content-center">
                @foreach ($character->equipment() as $equipment)
                    <div class="col-md-2">
                        @if ($equipment->has_image)
                            <img class="img-fluid rounded" src="{{ $equipment->imageUrl }}" data-toggle="tooltip" title="{{ $equipment->equipment->displayWithStats() }}" />
                        @elseif($equipment->equipment->imageurl)
                            <img class="img-fluid rounded" src="{{ $equipment->equipment->imageUrl }}" data-toggle="tooltip" title="{{ $equipment->equipment->displayWithStats() }}" />
                        @else
                            {!! $equipment->equipment->displayName !!}
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="text-right mb-4">
        <a href="{{ url($character->url . '/stats/logs') }}">View Logs...</a>
    </div>
@endsection
@section('scripts')
    <script>
        $('document').ready(function() {
            $('.stat-entry').on('click', function() {
                var id = $(this).data('id');
                loadModal("{{ url('character/' . $character->slug . '/stats') }}/" + id);
            });
        });
    </script>
@endsection
