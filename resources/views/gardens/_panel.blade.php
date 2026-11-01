<div class="scene" @if ($garden->has_background_image) data-palette-source="{{ $garden->backgroundImageUrl }}" @endif>
    <div class="{{ $garden->has_background_image ? 'garden-background' : 'garden-no-background' }}"
        style="{{ $garden->has_background_image ? 'background: linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(0, 0, 0, 0.04)), url(\'' . $garden->backgroundImageUrl . '\') center/cover no-repeat;' : '' }}">
        <div class="chip">{!! $garden->name !!}</div>
        <div class="chip" id="modeHint">
            {{ $readOnly ? 'Tend mode: click a ready plot to help care for it' : 'Place mode: drag seeds and animals onto plots' }}
        </div>
    </div>

    @if ($garden->plots->count())
        <div class="grid-wrap">
            <div class="plots" id="plots">
                @foreach ($garden->plots as $plot)
                    @php
                        $isPlotUnlocked = $user->gardenPlots->contains('plot_id', $plot->id) || hasUnlockedLimits($user, $plot);
                        if ($readOnly && !$isPlotUnlocked) {
                            continue;
                        }
                    @endphp
                    @include('gardens._plot', [
                        'plot' => $plot,
                        'readOnly' => $readOnly,
                        'user' => $user,
                        'isPlotUnlocked' => $isPlotUnlocked ?? false,
                    ])
                @endforeach
            </div>
        </div>

        @if (!$readOnly)
            <div class="toolbar">
                <div class="mb-3 d-flex justify-content-end">
                    {!! Form::open(['url' => 'gardens/care-all', 'class' => 'mr-2']) !!}
                    {!! Form::hidden('garden_id', $garden->id) !!}
                    {!! Form::submit('Care for All Ready', ['class' => 'btn btn-outline-primary']) !!}
                    {!! Form::close() !!}
                    {!! Form::open(['url' => 'gardens/collect-all']) !!}
                    {!! Form::hidden('garden_id', $garden->id) !!}
                    {!! Form::submit('Collect All Ready', ['class' => 'btn btn-outline-success']) !!}
                    {!! Form::close() !!}
                </div>
                <div class="toolbar-top d-flex justify-content-between align-items-center flex-wrap">
                    <strong>Inventory Bar</strong>
                    <div class="hint" id="toolbarHint">
                        Tools are click-to-equip. Seeds are drag-to-plant.
                    </div>
                </div>

                <div class="inventory">
                    <div class="section h-100">
                        <h3>Tools</h3>
                        <div class="tool-row d-flex">
                            <button class="slot tool active btn" data-mode="place" data-type="tool" data-name="None">
                                <div class="icon"><i class="fas fa-ban" aria-hidden="true"></i></div>
                                <div class="name">None</div>
                            </button>
                            <button class="slot tool btn" data-mode="tend" data-type="tool" data-name="Love & Affection">
                                <div class="icon"><i class="fas fa-tint" aria-hidden="true"></i></div>
                                <div class="name">Tend</div>
                            </button>
                            <button class="slot tool btn" data-mode="collect" data-type="tool" data-name="Collecting Basket">
                                <div class="icon"><i class="fas fa-shopping-basket" aria-hidden="true"></i></div>
                                <div class="name">Collect</div>
                            </button>
                            {{-- other tools here --}}
                            @foreach ($modifiers as $gardenObjectType => $objectModel)
                                @foreach ($objectModel as $model => $object)
                                    @foreach ($object as $id => $quantity)
                                        @include('gardens.selectors._modifier_selector', [
                                            'id' => $id,
                                            'object' => $object,
                                            'quantity' => $quantity,
                                            'gardenObjectType' => $gardenObjectType,
                                        ])
                                    @endforeach
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    <div class="section h-100">
                        <h3>Placeable Objects</h3>
                        <div class="asset-row d-flex" id="seedRow">
                            @foreach ($placeables as $gardenObjectType => $objectModel)
                                @foreach ($objectModel as $model => $object)
                                    @foreach ($object as $id => $quantity)
                                        @include('gardens.selectors._placeable_selector', [
                                            'id' => $id,
                                            'object' => $object,
                                            'quantity' => $quantity,
                                            'gardenObjectType' => $gardenObjectType,
                                        ])
                                    @endforeach
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="garden-status status alert alert-light mb-0 mt-3">Mode: Plant. Selected tool: None</div>
            </div>
        @elseif (Auth::check() && Auth::id() != $user->id && config('lorekeeper.garden.allow_users_to_water_gardens'))
            @php
                $guestWaterLimit = (int) config('lorekeeper.garden.user_guest_water_limit');
                $guestWatersUsed = Auth::user()?->activity?->garden_guest_waters_today ?? 0;
                $guestWatersRemaining = $guestWaterLimit ? max(0, $guestWaterLimit - $guestWatersUsed) : null;
            @endphp
            <div class="garden-status status alert {{ $guestWatersRemaining === 0 ? 'alert-warning' : 'alert-info' }} mb-0 mt-3">
                @if ($guestWatersRemaining === 0)
                    You have used all of your guest waterings for today.
                @elseif ($guestWatersRemaining === null)
                    Click a highlighted plant to water it. Guest watering is unlimited.
                @else
                    Click a highlighted plant to water it. You have <strong>{{ $guestWatersRemaining }}</strong> guest {{ Str::plural('watering', $guestWatersRemaining) }} remaining today.
                @endif
            </div>
        @endif
    @else
        <div class="alert alert-info mb-0">
            There are currently no available plots in this garden.
        </div>
    @endif
</div>
