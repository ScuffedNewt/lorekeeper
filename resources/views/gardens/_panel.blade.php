

<div class="scene">
    <div class="{{ $garden->has_background_image ? 'garden-background' : 'garden-no-background' }}" style="{{ $garden->has_background_image ? 'background: linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(0, 0, 0, 0.04)), url(\'' . $garden->backgroundImageUrl . '\') center/cover no-repeat;' : '' }}">
        <div class="chip">{!! $garden->name !!}</div>
        <div class="chip" id="modeHint">
            Plant mode: drag seeds onto soil plots
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
                        'plot'           => $plot,
                        'readOnly'       => $readOnly,
                        'user'           => $user,
                        'isPlotUnlocked' => $isPlotUnlocked ?? false,
                    ])
                @endforeach
            </div>
        </div>

        <div class="toolbar">
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
                        <button class="slot tool active btn" data-type="tool" data-name="None">
                            <div class="icon">✖</div>
                            <div class="name">None</div>
                        </button>
                        {{-- <button class="slot tool btn" data-type="tool" data-name="Watering Can">
                            <div class="icon">💧</div>
                            <div class="name">Water</div>
                        </button>
                        <button class="slot tool btn" data-type="tool" data-name="Shovel">
                            <div class="icon">🪓</div>
                            <div class="name">Till</div>
                        </button> --}}
                    </div>
                </div>
                <div class="section h-100">
                    <h3>Seeds & Animals</h3>
                    <div class="seed-row d-flex" id="seedRow">
                        @foreach ($placeableUserObjects as $gardenObjectType => $objectModel)
                            @foreach ($objectModel as $model => $object)
                                @foreach ($object as $id => $quantity)
                                    @include('gardens.selectors._placeable_selector', [
                                        'id'               => $id,
                                        'object'           => $object,
                                        'quantity'         => $quantity,
                                        'gardenObjectType' => $gardenObjectType
                                    ])
                                @endforeach
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="status alert alert-light mb-0 mt-3" id="status">Mode: Plant. Selected tool: None</div>
        </div>

        <div class="ghost" id="ghost">🥝</div>
    @else
        <div class="alert alert-info mb-0">
            There are currently no available plots in this garden.
        </div>
    @endif
</div>