<!--comment this in if you want the daily image to show on the daily page as well and not only on the index --->
<!---
<div class="text-center">
    @if ($daily->has_image && !$wheel->backgroundUrl)
<img src="{{ $daily->dailyImageUrl }}" style="max-width:100%" alt="{{ $daily->name }}" />
@endif
</div>
--->

<div class="text-{{ $wheel->alignment }}" style="background-size:cover; background-image:url('{{ $wheel->backgroundUrl }}');">
    <div class="row justify-content-center {{ $wheel->marginAlignment() }}" style="width:{{ $wheel->size }}px;height:50px;">
        <img src="{{ $wheel->stopperUrl }}" style="max-height:50px;">
    </div>
    <div id="#canvas-container" class="w-100 {{ $wheel->marginAlignment() }}" style="max-width:{{ $wheel->size }}px;max-height:{{ $wheel->size }}px;">
        <canvas class="@if ($isDisabled) disabled @endif" id='canvas' width="{{ $wheel->size }}" height="{{ $wheel->size }}" onClick="calcPrize();" style="cursor: pointer;">
            Canvas not supported, use another browser.
        </canvas>
    </div>
</div>

<div class="text-center">
    <p>{!! $daily->parsed_description !!}</p>
</div>

@if (Auth::user())
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

@if ($daily->progress_display != 'none')
    <div class="card mt-5">
        <div class="card-header">
            <h4 class="m-0 align-items-center">Prize Pool</h4>
        </div>

        <div class="card-body row p-0 m-auto w-100">
            @foreach ($daily->rewards()->get() as $reward)
                <div class="col-lg-2 col-6 w-100 }} text-center justify-content-center border p-0">
                    <div class="row w-100 p-1 m-auto btn-primary">
                        <div class="col-lg col-6 h-100">
                            <h5 class="p-1 m-0">{{ $loop->index + 1 }}</h5>
                        </div>
                        <div class="col p-0">
                        </div>
                    </div>
                    <div class="row w-100 p-0 m-auto">
                        @if ($daily->progress_display == 'all')
                            <div class="col-6">
                                @if ($reward->rewardImage)
                                    <div class="row justify-content-center"><img src="{{ $reward->rewardImage }}" alt="{{ $reward->reward()->first()->name }}" style="max-width:75px;width:100%;" /></div>
                                @endif
                                <div class="row justify-content-center">{{ $reward->quantity }} {{ $reward->reward()->first()->name }}</div>

                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
@section('scripts')
    <script>
        // initialize the wheel!
        let theWheel = new Winwheel({
            'numSegments': "{{ $wheel->segment_number }}", // Specify number of segments editable from admin panel
            'outerRadius': "{{ $wheel->size / 2 - 10 }}", // Set outer radius so wheel fits inside the background. Derived from size from admin panel.
            'drawMode': "{{ $wheel->wheel_extension ? 'image' : 'none' }}", // drawMode must be set to image if wheel image is set for the wheel.
            'drawText': true, // Need to set this true if want code-drawn text on image wheels.
            'textFontSize': "{{ $wheel->text_fontsize }}", // editable from admin panel
            'textOrientation': "{{ $wheel->text_orientation }}", // curved or vertical editable from admin panel
            'textAlignment': 'outer',
            'textMargin': 5,
            'textFontFamily': 'monospace',
            'textLineWidth': 1,
            'imageOverlay': false, //if ya want to see red lines of the wheel overlaid on the image set this to true but not in production!
            'textFillStyle': 'black',
            'rotationAngle': (sessionStorage.getItem("rotationAngle")) ? parseInt(sessionStorage.getItem("rotationAngle")) : 0,
            'segments': {!! $wheel->segmentStyleReplace !!},
            'animation': // Specify the animation to use.
            {
                'type': 'spinToStop',
                'duration': 5, // Duration in seconds.
                'spins': 8, // Number of complete spins.
                'callbackFinished': alertPrize
            }
        });

        window.onresize = resize; // make the wheel responsive

        // Load in the image if one was set
        let loadedImg = new Image();
        loadedImg.onload = function() {
            theWheel.wheelImage = loadedImg; // Make wheelImage equal the loaded image object.
            theWheel.draw(); // Also call draw function to render the wheel.
        }
        loadedImg.src = "{{ $wheel->wheelUrl }}";

        //it is not spinning right now
        let wheelSpinning = false;

        //resize initially
        resize();

        function resize() {
            var container = document.getElementById("#canvas-container");
            canvas.width = container.clientWidth;
            canvas.height = container.clientWidth;
            theWheel.outerRadius = container.clientWidth / 2 - 10;
            theWheel.centerX = container.clientWidth / 2;
            theWheel.centerY = container.clientHeight / 2;
            loadedImg.width = container.clientWidth;
            loadedImg.height = container.clientWidth;
            theWheel.wheelImage = loadedImg;
            theWheel.draw()
        }


        function calcPrize() {
            if (!$('#canvas').hasClass('disabled')) {
                //ajax call to the backend to roll the prize when the wheel is clicked.
                $.ajax({
                    type: "POST",
                    url: "{{ url(__('dailies.dailies') . '/' . $daily->id) }}",
                    data: {
                        daily_id: "{{ $daily->id }}",
                        _token: '{{ csrf_token() }}'
                    },
                }).done(function(res) {
                    var prizeSegment = res;
                    // Ensure that spinning can't be clicked again while already running.
                    if (prizeSegment && wheelSpinning == false) {
                        var stopAt = theWheel.getRandomForSegment(prizeSegment);
                        // set stop angle for the prize
                        theWheel.animation.stopAngle = stopAt;
                        // reset to 0 so it always spins well
                        theWheel.rotationAngle = 0;
                        // Based on the power level selected adjust the number of spins for the wheel, the more times is has
                        // to rotate with the duration of the animation the quicker the wheel spins.
                        theWheel.animation.spins = 5;

                        // Disable the spin button so can't click again while wheel is spinning.
                        $('#canvas').addClass('disabled');

                        // Begin the spin animation by calling startAnimation on the wheel object.
                        theWheel.startAnimation();

                        // Set to true so that power can't be changed and spin button re-enabled during
                        // the current animation. The user will have to reset before spinning again.
                        wheelSpinning = true;
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert("Woops- something went wrong! Please refresh the page and try again. If the error persists, please report it to the site owners!");
                });
            }
        }

        // Called when the animation has finished.
        function alertPrize(indicatedSegment) {
            // we dont want the wheel to reset after a spin
            sessionStorage.setItem("rotationAngle", theWheel.getRotationPosition());
            window.location.reload();
        }
    </script>
@endsection
