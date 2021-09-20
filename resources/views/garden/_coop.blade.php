
<!--------------------------------------------->
<div class="container" id="content">
    <!--------------------------------------------->
    <div class="d-flex justify-content-between bg-light" style="font-size: 100% !important; overflow-y: auto;">
        <div class="col card pb-2" style=" max-height:55em;">
            <h5 class="mt-1">Coop Nests</h5>
            <div class="row justify-content-center">
            @foreach($plots->chunk(3) as $chunk)
                @foreach($chunk as $plot)
                <div class="card text-center m-1 col-lg-3 col-md-4" style="min-width: 14em; min-height: 14em; background-color: rgb(100, 60, 41); justify-content: center; margin:0; padding:0;">
                    <!---- if user owns plot --->
                        @if(Auth::user()->gardenplots->contains('plot_id', $plot->id))
                        @php
                            $userPlot = Auth::user()->gardenplots->where('plot_id', $plot->id)->first();
                            $now = Carbon\Carbon::now();
                        @endphp
                        @if($userPlot->started_at != null)
                            <!----If there is a plant growing --->
                            @if($userPlot->water_at > $now && $userPlot->waterings < $userPlot->item->tags->where('tag', 'coop')->first()->data['waterings'])
                                <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ $userPlot->item->imageUrl }}" data-toggle="tooltip" title="{{ $userPlot->waterings }} {{ $userPlot->waterings == 1 ? 'feeding' : 'feedings' }} done!" style="width: 10em; height: 10em;">
                                <span class="text-muted"> Housing {!! $userPlot->item->name !!}</span>
                                <div id="{{ $userPlot->id }}-water-text" class="text-white">Next feeding {{ $userPlot->water_at }}</div>
                                <p class="text-white">Started {!! pretty_date($userPlot->started_at)!!}</p>
                                <!---- Water times -->
                                <script>
                                    function waterCount(id, timer) {
                                    setInterval(function(){ 
                                        
                                    var date = new Date(timer);
                                    var now = new Date();
                                    // count down time difference between now and date
                                    var diff = date.getTime() - now.getTime();
                                    var time = new Date(diff);

                                    var seconds = time.getUTCSeconds();
                                    if(seconds < 10) seconds = "0" + seconds;
                                    var minutes = time.getUTCMinutes();
                                    if(minutes < 10) minutes = "0" + minutes;
                                    var hours = time.getUTCHours();
                                    if(hours < 10) hours = "0" + hours;

                                    var text = "Next feeding in "+ hours + ":" + minutes + ":" + seconds;
                                    $("#"+id+"-water-text").text(text);
                                    }, 1000)}

                                    var wateringLeft = Date.parse("<?php echo $userPlot->water_at ?>");
                                    var waterTextId = "<?php echo $userPlot->id ?>"

                                    setInterval(waterCount(waterTextId, wateringLeft), 1000);
                                </script>
                                <!------------------>
                                <button type="button" class="skull-button" value="{{ $userPlot->id }}">
                                    <i class="fas fa-skull-crossbones"></i>
                                </button>
                            @else
                                <!--- if we can water, check if the plant is complete or if it's ready to be watered again -->
                                @if($userPlot->waterings >= $userPlot->item->tags->where('tag', 'seed')->first()->data['waterings'])
                                    <i class="fas fa-seedling text-white mt-1"></i>
                                    <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ asset('images/data/seeds/seed.png') }}" data-toggle="tooltip" title="{{ $userPlot->waterings }} {{ $userPlot->waterings == 1 ? 'watering' : 'waterings' }} done!" style="width: 10em; height: 10em;">
                                    {!! Form::open(['url' => 'garden/claim/' . $userPlot->id]) !!}
                                    {!! Form::submit('Claim!', ['class' => 'btn btn-sm btn-primary']) !!}
                                    {!! Form::close() !!}
                                <!---->
                                @else
                                    <button type="button" class="btn btn-primary" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="waterPlot({{ $userPlot->id }})">
                                        <img class="img-fluid image-zoom my-auto mx-auto pt-2" src="{{ asset('images/data/seeds/seed.png') }}" data-toggle="tooltip" title="{{ $userPlot->waterings }} {{ $userPlot->waterings == 1 ? 'watering' : 'waterings' }} done!" style="width: 9em; height: 9em;">
                                        <br> Water <i class="fas fa-water"></i>
                                        <p class="text-white">Started {!! pretty_date($userPlot->started_at)!!}</p>
                                    </button>
                                @endif
                            @endif
                        @else
                        <!----If there is NO plant growing --->
                        <i class="fas fa-seedling text-white m-1"></i>
                            <button type="button" class="seed-button-plant btn btn-success hide" style="width:100%; height :100%;" id="{{ $userPlot->id }}-button" onclick="placeCoop({{ $userPlot->id }})">
                                Place Animal!
                            </button>
                        @endif

                    <!---- if not owned plot --->
                        @else
                            <div class="p-5 container text-white">
                                This plot {{ $plot->free ? 'is free!' : 'costs ' . $plot->plot_cost . ' ' . $plot->currency->name }}
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
        <!-------------------- SEEDS -------------------------->
        <div class="text-center card" style="overflow-y: scroll; overflow-x: hidden; flex 0 0 7em; min-width: 11em; max-width: 11em; max-height:51em;">
            <h5 class="text-left">Coop Store</h5> 
            <div class=" p-2 btn-group-vertical btn-group-toggle" id="coopSelector" data-toggle="buttons">
                <label type="button" class="btn  my-1 btn-block seed-button" type="radio" autocomplete="off" >
                    <input type="radio" name="options" autocomplete="off" value="0" >
                        <i class="fas fa-times fa-5x text-white"></i>
                        <div class="text-small text-white text-center" style="font-size:1rem;">No Coop Animal</div>
                </label>
                @foreach($userCoops as $coop)
                    @foreach($coop->chunk(4) as $chunk)
                        @foreach($chunk as $stack)
                            <label type="button" class="btn  my-1 btn-block seed-button" type="radio" autocomplete="off">
                                <input type="radio" name="options" autocomplete="off" value="{{$stack->first()->id}}" >
                                    <img class="img-fluid image-zoom"  src="{{ $stack->first()->imageUrl }}" style="width: 100%">
                                <div class="text-small text-white text-center  text-wrap" style="font-size:1rem;"> {!!$stack->first()->name !!} <br>Quantity: <br> x{{ $stack->sum('pivot.count') }}</div>
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
    let selectedCoop;
    
    $("#coopSelector input:radio").change(function() {
        selectedCoop = $(this).val();
        console.log(selectedCoop);
        if(selectedCoop == null || selectedCoop == 0) {
            $(".seed-button-plant").each( function(){
            $(this).addClass('hide');
            });
        }
        else {
            $(".seed-button-plant").each( function() {
                $(this).removeClass('hide');
            });
        }
    });

    $('.skull-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('garden/skull') }}" + '/' + $(this).val(), 'Remove Animal?');
    });

    function placeCoop(plotID) {
        if(selectedCoop == null){
            alert("Please select a coop!");
        }
        else {
            $.ajax({
            url: "{{ url('garden/plant') }}",
            method: 'post',
            data:{
                _token: '{{ csrf_token() }}',
                plotID:plotID,
                coopID:selectedCoop,
                type:'coop'
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

    function waterPlot(plotID) {
        $.ajax({
            url: "{{ url('garden/water') }}",
            method: 'post',
            data:{
                _token: '{{ csrf_token() }}',
                plotID:plotID,
                type:'coop'
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
                type:'coop'
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