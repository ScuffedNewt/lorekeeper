
<!--------------------------------------------->
<div class="container" id="content">
    @if($plots->first()->plot_type == 'Seed')
    <h5 class="h5">Modifiers</h5>
    <div class=" bg-light overflow-auto card" style="overflow-x: scroll;">
        <div class=" p-2 btn-group-toggle" id="modifierSelector" data-toggle="buttons" >
            <label type="button" class="modifier-btn btn mr-2 modifier-button" type="radio" autocomplete="off">
                <input type="radio" name="options" autocomplete="off" value="0" >
                    <i class="fas fa-times fa-5x text-white"></i>
                    <div class="text-small font-weight-bold text-secondary">No Modifier</div>
            </label>
            @foreach($userModifiers as $modifier)
                @foreach($modifier->chunk(4) as $chunk)
                    @foreach($chunk as $stack)
                        <label type="button" class="modifier-btn btn mr-2 modifier-button" style="height: 100%" type="radio" autocomplete="off">
                            <input type="radio" name="options" autocomplete="off" value="{{ $stack->first()->id }}" >
                            <img class="img-fluid image-zoom" src="{{ $stack->first()->imageUrl }}" style="height: 6em">
                            <div class="text-small font-weight-bold text-secondary"> {!! $stack->first()->name !!} <br>Quantity: x{{ $stack->sum('pivot.count') }}</div>
                        </label>
                    @endforeach
                @endforeach
            @endforeach
        </div>
    </div>
    @endif
    <!--------------------------------------------->
    <div class="d-flex justify-content-between bg-light" style="font-size: 100% !important; overflow-y: auto;" style="max-height:55em;">
        <div class="col card pb-2 border-0">
            <h5 class="mt-1">{!! $plots->first()->paneltitle !!}</h5>
            @include('garden._clear_all', ['type' => $plots->first()->plot_type])
            <div class="row justify-content-center">
            @foreach($plots->sortByDesc('free')->chunk(3) as $chunk)
                @foreach($chunk as $plot)
                <div class="card text-center m-1 col-lg-3 col-md-4" style="min-width: 14em; min-height: 14em; background-image: url({{ asset($plot->image)}}); justify-content: center; margin:0; padding:0;">
                    <!---- if user owns plot --->
                        @if(Auth::user()->gardenplots->contains('plot_id', $plot->id))
                        @php
                            $userPlot = Auth::user()->gardenplots->where('plot_id', $plot->id)->first();
                        @endphp
                            @if($userPlot->started_at != null)
                                <!----If there is a plant growing / animal placed --->
                                @if($userPlot->wateringTime)
                                    @if($plot->plot_type == 'Seed')
                                        <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ asset($userPlot->item->tags->where('tag', 'seed')->first()->stageImage($userPlot->waterings)) }}" 
                                        data-toggle="tooltip" title="{{ $userPlot->waterings }} {{ $userPlot->waterings == 1 ? 'watering' : 'waterings' }} done!" style="width: 10em; height: 10em;">
                                        <div class="text-muted mt-4"> {!! $userPlot->displayHolding !!} </div>
                                    @else
                                        <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ $userPlot->item->imageUrl }}" 
                                        data-toggle="tooltip" title="{{ $userPlot->waterings }} {{ $userPlot->waterings == 1 ? 'feeding' : 'feedings' }} done!" style="width: 10em; height: 10em;">
                                        <div class="text-muted mt-4"> {!! $userPlot->displayHolding !!} </div>
                                        <div class="progress mx-4">
                                            @php 
                                                if($userPlot->waterings != 0) { 
                                                    $style = ($userPlot->waterings / $userPlot->item->tags->where('tag', strtolower($plot->plot_type))->first()->data['feedings']) * 100;
                                                } 
                                                else $style = 0; 
                                            @endphp
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" aria-valuenow="{{ $userPlot->waterings }}" 
                                            aria-valuemin="0" aria-valuemax="{{ $userPlot->item->tags->where('tag', strtolower($plot->plot_type))->first()->data['feedings'] }}" 
                                            style="width: {{$style}}%">{{ $userPlot->waterings ?? 0 }} / {{ $userPlot->item->tags->where('tag', strtolower($plot->plot_type))->first()->data['feedings']}}
                                            </div>
                                        </div>
                                    @endif    
                                    <div id="{{ $userPlot->id }}-water-text" class ="text-white">Next {{ $plot->plot_type == 'Seed' ? 'watering' : 'feeding'}} at {{ $userPlot->water_at->format('H:i:s') }}</div>
                                    <p class="text-white">Started {!! pretty_date($userPlot->started_at)!!}</p>
                                    <!---- Water times -->
                                    <script>
                                        var wateringLeft = Date.parse("<?php echo $userPlot->water_at ?>");
                                        var waterTextId = "<?php echo $userPlot->id ?>"
                                        if(!isSafari) setInterval(waterCount(waterTextId, wateringLeft), 1000);
                                    </script>
                                    <!------------------>
                                    <button type="button" class="skull-button-garden" value="{{ $userPlot->id }}">
                                        <i class="fas fa-skull-crossbones"></i>
                                    </button>
                                @else
                                    <!--- if we can water, check if the plant / animal is complete or if it's ready to be watered again -->
                                    @if($userPlot->waterings >= $userPlot->item->tags->where('tag', strtolower($plot->plot_type))->first()->data[($plot->plot_type == 'Seed' ? 'waterings' : 'feedings')])
                                        <i class="fas fa-seedling text-white mt-1"></i>
                                        @if($plot->plot_type == 'Seed')
                                            
                                            <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ asset($userPlot->item->tags->where('tag', 'seed')->first()->stageImage($userPlot->waterings)) }}" data-toggle="tooltip" title="{{ $userPlot->waterings }} {{ $userPlot->waterings == 1 ? 'watering' : 'waterings' }} done!" style="width: 10em; height: 10em;">
                                        @else
                                            <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ $userPlot->item->imageUrl }}" data-toggle="tooltip" title="{{ $userPlot->waterings }} {{ $userPlot->waterings == 1 ? 'feeding' : 'feedings' }} done!" style="width: 10em; height: 10em;">
                                            <div class="progress mx-4 mb-2 mt-4">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="1" style="width: 100%">{{ $userPlot->waterings ?? 0 }} / {{ $userPlot->item->tags->where('tag', strtolower($plot->plot_type))->first()->data['feedings']}}</div>
                                            </div>
                                        @endif
                                        {!! Form::open(['url' => 'garden/claim/' . $userPlot->id]) !!}
                                        {!! Form::submit('Claim!', ['class' => 'btn btn-sm btn-primary mb-1']) !!}
                                        {!! Form::close() !!}
                                    <!---->
                                    @else
                                        @if($plot->plot_type == 'Seed')
                                            <button type="button" class="water-click-button btn btn-primary" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="managePlot({{ $userPlot->id }})">
                                                <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ asset($userPlot->item->tags->where('tag', 'seed')->first()->stageImage($userPlot->waterings)) }}" 
                                                data-toggle="tooltip" title="{{ $userPlot->waterings }} {{ $userPlot->waterings == 1 ? 'watering' : 'waterings' }} done!" style="width: 9em; height: 9em;">
                                                <br> Water <i class="fas fa-water"></i>
                                                <p class="text-white">Started {!! pretty_date($userPlot->started_at)!!}</p>
                                            </button>
                                        @else
                                            <button type="button" class="water-click-button btn btn-warning text-white" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="managePlot({{ $userPlot->id }})">
                                                <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ $userPlot->item->imageUrl }}" 
                                                data-toggle="tooltip" title="{{ $userPlot->waterings }} {{ $userPlot->waterings == 1 ? 'feeding' : 'feedings' }} done!" style="width: 9em; height: 9em;">
                                                <br> Feed <i class="fas fa-carrot"></i>
                                                <p class="text-white">Started {!! pretty_date($userPlot->started_at)!!}</p>
                                            </button>
                                        @endif
                                    @endif
                                @endif
                            @else
                            <!----If there is NO plant growing --->
                            <i class="fas fa-seedling text-white m-1"></i>
                                @if($userPlot->modifiers != null)
                                        @php $mods = json_decode($userPlot->modifiers, true); @endphp
                                        @if($mods['fertiliser'])
                                            <p class="text-white"><i class="fas fa-star"></i> Fertiliser Quality: {{ $mods['fertiliser'] }}</p>
                                        @endif
                                @endif
                                <button type="button" class="seed-button-plant btn btn-success hide" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="addItem({{ $userPlot->id }})">
                                    Plant Seed!
                                </button>
                                <button type="button" class="mod-button-plant btn btn-warning text-white hide" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="addModifier({{ $userPlot->id }})">
                                    Apply Modifier!
                                </button>
                            @endif
                    <!---- if not owned plot --->
                        @else
                            <div class="p-5 container text-white">
                                This plot {{ $plot->isFree ? 'is free!' : 'costs ' . $plot->plot_cost . ' ' . $plot->currency->name }}
                                {!! Form::open(['url' => 'garden/plots/purchase/' . $plot->id]) !!}
                                {!! Form::submit('Purchase plot?', ['class' => 'btn btn-sm btn-primary mt-3']) !!}
                                {!! Form::close() !!}
                            </div>
                        @endif
                    </div>
                @endforeach
            @endforeach
            </div>
        
        </div>
        <!-------------------- SEEDS | ANIMALS -------------------------->
        <div class="text-center card" style="overflow-y: scroll; overflow-x: hidden; flex 0 0 7em; min-width: 11em; max-width:11em;">
            <h5 class="text-left">{{ $plots->first()->plotItems}}</h5> 
            <div class=" p-2 btn-group-vertical btn-group-toggle" id="itemSelector" data-toggle="buttons">
                <label type="button" class="btn  my-1 btn-block seed-button" type="radio" autocomplete="off" >
                    <input type="radio" name="options" autocomplete="off" value="0" >
                        <i class="fas fa-times fa-5x text-white"></i>
                        <div class="text-small text-white text-center" style="font-size:1rem;">No {{ $plots->first()->plot_type == 'Seed' ? 'Seeds' : 'Animals' }}</div>
                </label>
                @foreach($userItems as $item)
                    @foreach($item->chunk(4) as $chunk)
                        @foreach($chunk as $stack)
                            <label type="button" class="btn  my-1 btn-block seed-button" type="radio" autocomplete="off">
                                <input type="radio" name="options" autocomplete="off" value="{{$stack->first()->id}}" >
                                    <img class="img-fluid image-zoom"  src="{{ $stack->first()->imageUrl }}" style="width: 50%">
                                <div class="text-small text-white text-center mt-2 text-wrap" style="font-size:1rem;"> {!!$stack->first()->name !!} <br>Quantity: <br> x{{ $stack->sum('pivot.count') }}</div>
                            </label>
                        @endforeach
                    @endforeach
                @endforeach
            </div>
        </div>
        <!--------------------------------------------->
    </div>
</div>
<script>
    let selectedItem;
    let selectedModifier;
    
    $("#itemSelector input:radio").change(function() {
        selectedItem = $(this).val();
        if(selectedItem == null || selectedItem == 0) {
            $(".seed-button-plant").each( function(){
            $(this).addClass('hide');
            })
        }
        else {
            $(".seed-button-plant").each( function() {
                $(this).removeClass('hide');
            })
            $(".mod-button-plant").each( function(){
                $(this).addClass('hide');
            })
        }
    });

    $("#modifierSelector input:radio").change(function() {
        if('<?php echo $plots->first()->plot_type; ?>' == 'Seed') {
            selectedModifier = $(this).val();
            if(selectedModifier == null || selectedModifier ==  0){
                $(".mod-button-plant").each( function(){
                    $(this).addClass('hide');
                })
            }
            else {
                $(".seed-button-plant").each( function() {
                    $(this).addClass('hide');
                })
                $(".mod-button-plant").each( function(){
                    $(this).removeClass('hide');
                })
            }
        }
    });

    $('.skull-button-garden').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('garden/skull') }}" + '/' + $(this).val(), ('<?php echo $plots->first()->plot_type; ?>' == 'Seed' ? 'Destroy Crop?' : 'Remove Animal?'));
    });

    function addItem(plotID) {
        // disable plant class
        $(".seed-button-plant").each( function(){
            $(this).addClass('hide');
        });
        if(selectedItem == null){
            alert("Please select a" + ('<?php echo $plots->first()->plot_type; ?>' == 'Seed' ? ' seed to plant!' : 'n animal to add!'));
        }
        else {
            $.ajax({
            url: "{{ url('garden/plant') }}",
            method: 'post',
            data:{
                _token: '{{ csrf_token() }}',
                plotID:plotID,
                seedID:selectedItem,
                },
            dataType: 'json',
            success: function(data) {
                refreshThePage();
            },
            error: function(error) {
                refreshThePage();
            }
        });
        }
    }

    function addModifier(plotID) {
        if(selectedModifier == null){
            alert("Please select a modifier!");
        }
        else {
            $.ajax({
            url: "{{ url('garden/mod') }}",
            method: 'post',
            data:{
                _token: '{{ csrf_token() }}',
                plotID:plotID,
                modID:selectedModifier
                },
            dataType: 'json',
            success: function(data) {
                refreshThePage();
            },
            error: function(error) {
                refreshThePage();
            }
        });
        }
    }

    function managePlot(plotID) {
        // disable water class
        $(".water-click-button").each( function(){
            $(this).addClass('hide');
        });
        $.ajax({
            url: "{{ url('garden/water') }}",
            method: 'post',
            data:{
                _token: '{{ csrf_token() }}',
                plotID:plotID,
                },
            dataType: 'json',
            success: function(data) {
                refreshThePage();
            },
            error: function(error) {
                refreshThePage();
            }
        });
    }

    function clearPlot(plotID) {
        $.ajax({
            url: "{{ url('garden/clear') }}",
            method: 'post',
            data:{
                _token: '{{ csrf_token() }}',
                plotID:plotID,
                },
            dataType: 'json',
            success: function(data) {
                refreshThePage();
            },
            error: function(error) {
                refreshThePage();
            }
        });
    }

    function refreshThePage(){
        location.reload();
    }
</script>