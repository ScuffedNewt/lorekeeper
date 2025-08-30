@extends('character.design.layout')

@section('design-title')
    Request (#{{ $request->id }}) :: Image
@endsection

@section('design-content')
    {!! breadcrumbs(['Design Approvals' => 'designs', 'Request (#' . $request->id . ')' => 'designs/' . $request->id, 'Masterlist Image' => 'designs/' . $request->id . '/image']) !!}

    @include('character.design._header', ['request' => $request])

    <h2>Masterlist Image</h2>

    @if ($request->has_image)
        <div class="card mb-3">
            <div class="card-body bg-secondary text-white">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h3 class="text-center">Main Image</h3>
                        <div class="d-flex justify-content-center gap-2 mb-2">
                            <div class="btn-group" role="group" aria-label="Annotator mode">
                                <button id="mode-measure" type="button" class="btn btn-primary active">Measure</button>
                                <button id="mode-colour" type="button" class="btn btn-outline-primary">Colour Pick</button>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="img-annotator" id="annotator" style="display:inline-block; position:relative;">
                                <img id="request-image" src="{{ $request->imageUrl }}?v={{ $request->updated_at->timestamp }}" class="mw-100" alt="Request {{ $request->id }}" style="display:block; max-width:100%; height:auto;" />

                                {{-- measurement --}}
                                <svg id="measure-svg" class="overlay" style="position:absolute; inset:0; width:100%; height:100%; touch-action:none; cursor:crosshair; z-index:2;" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                                </svg>

                                {{-- values --}}
                                <canvas id="request-canvas" style="position:absolute; inset:0; z-index:3; display:none; cursor:crosshair;">
                                </canvas>

                                <small class="d-block my-2 text-center">
                                    Measurement Controls: Click-drag to draw. Click a line to select. Drag line to move. Drag end dots to resize.
                                    Press Delete/Backspace to remove selection. Esc will remove all measurements.
                                </small>
                            </div>
                            <div class="text-center">
                                <a href="{{ $request->imageUrl }}?v={{ $request->updated_at->timestamp }}" class="btn btn-primary btn-sm" data-lightbox="entry" data-title="Request #{{ $request->id }}">
                                    View Image
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h3 class="text-center">Thumbnail Image</h3>
                        <div class="text-center">
                            <a href="{{ $request->thumbnailUrl }}?v={{ $request->updated_at->timestamp }}" data-lightbox="entry" data-title="Request #{{ $request->id }} thumbnail">
                                <img src="{{ $request->thumbnailUrl }}?v={{ $request->updated_at->timestamp }}" class="mw-100" alt="Thumbnail for request {{ $request->id }}" />
                            </a>
                            <h5 class="text-center mt-2">Check Values</h5>
                            <p id="values"></p>
                        </div>
                    </div>
                </div>
            </div>

            @if (!($request->status == 'Draft' && $request->user_id == Auth::user()->id))
                <div class="card-body">
                    <h4 class="mb-3">Credits</h4>
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-4">
                            <h5>Design</h5>
                        </div>
                        <div class="col-lg-8 col-md-6 col-8">
                            @foreach ($request->designers as $designer)
                                <div>{!! $designer->displayLink() !!}</div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-4">
                            <h5>Art</h5>
                        </div>
                        <div class="col-lg-8 col-md-6 col-8">
                            @foreach ($request->artists as $artist)
                                <div>{!! $artist->displayLink() !!}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if (($request->status == 'Draft' && $request->user_id == Auth::user()->id) || ($request->status == 'Pending' && Auth::user()->hasPower('manage_characters')))
        @if ($request->status == 'Draft' && $request->user_id == Auth::user()->id)
            <p>Select the image you would like to use on the masterlist and an optional thumbnail. Please only upload images that you are allowed to use AND are able to credit to the artist! Note that while staff members cannot edit your uploaded image,
                they may choose to recrop or upload a different thumbnail.</p>
        @else
            <p>As a staff member, you may modify the thumbnail of the uploaded image and/or the credits, but not the image itself. If you have recropped the thumbnail, you may need to hard refresh to see the new one.</p>
        @endif
        {!! Form::open(['url' => 'designs/' . $request->id . '/image', 'files' => true]) !!}
        @if ($request->status == 'Draft' && $request->user_id == Auth::user()->id)
            <div class="form-group">
                {!! Form::label('Image') !!} {!! add_help('This is the image that will be used on the masterlist. Note that the image is not protected in any way, so take precautions to avoid art/design theft.') !!}
                <div class="custom-file">
                    {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
                    {!! Form::file('image', ['class' => 'custom-file-input', 'id' => 'mainImage']) !!}
                </div>
            </div>
        @else
            <div class="form-group">
                {!! Form::checkbox('modify_thumbnail', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('modify_thumbnail', 'Modify Thumbnail', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Toggle this option to modify the thumbnail, otherwise only the credits will be saved.') !!}
            </div>
        @endif
        @if (config('lorekeeper.settings.masterlist_image_automation') === 1)
            @if (config('lorekeeper.settings.masterlist_image_automation_hide_manual_thumbnail') === 0 || Auth::user()->hasPower('manage_characters'))
                <div class="form-group">
                    {!! Form::checkbox('use_cropper', 1, 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'useCropper']) !!}
                    {!! Form::label('use_cropper', 'Use Thumbnail Automation', ['class' => 'form-check-label ml-3']) !!} {!! add_help('A thumbnail is required for the upload (used for the masterlist). You can use the Thumbnail Automation, or upload a custom thumbnail.') !!}
                </div>
            @else
                {!! Form::hidden('use_cropper', 1) !!}
            @endif
            <div class="card mb-3" id="thumbnailCrop">
                <div class="card-body">
                    <div id="cropSelect">By using this function, the thumbnail will be automatically generated from the full image.</div>
                    {!! Form::hidden('x0', 1) !!}
                    {!! Form::hidden('x1', 1) !!}
                    {!! Form::hidden('y0', 1) !!}
                    {!! Form::hidden('y1', 1) !!}
                </div>
            </div>
        @else
            <div class="form-group">
                {!! Form::checkbox('use_cropper', 1, 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'useCropper']) !!}
                {!! Form::label('use_cropper', 'Use Image Cropper', ['class' => 'form-check-label ml-3']) !!} {!! add_help('A thumbnail is required for the upload (used for the masterlist). You can use the image cropper (crop dimensions can be adjusted in the site code), or upload a custom thumbnail.') !!}
            </div>
            <div class="card mb-3" id="thumbnailCrop">
                <div class="card-body">
                    <div id="cropSelect">Select an image to use the thumbnail cropper.</div>
                    <img src="{{ $request->imageUrl }}" id="cropper" class="hide" alt="" />
                    {!! Form::hidden('x0', null, ['id' => 'cropX0']) !!}
                    {!! Form::hidden('x1', null, ['id' => 'cropX1']) !!}
                    {!! Form::hidden('y0', null, ['id' => 'cropY0']) !!}
                    {!! Form::hidden('y1', null, ['id' => 'cropY1']) !!}
                </div>
            </div>
        @endif
        @if (
            ((config('lorekeeper.settings.masterlist_image_automation') === 0 || config('lorekeeper.settings.masterlist_image_automation_hide_manual_thumbnail') === 0) && config('lorekeeper.settings.hide_manual_thumbnail_image_upload') === 0) ||
                Auth::user()->hasPower('manage_characters'))
            <div class="card mb-3" id="thumbnailUpload">
                <div class="card-body">
                    {!! Form::label('Thumbnail Image') !!} {!! add_help('This image is shown on the masterlist page.') !!}
                    <div class="custom-file">
                        {!! Form::label('thumbnail', 'Choose thumbnail...', ['class' => 'custom-file-label']) !!}
                        {!! Form::file('thumbnail', ['class' => 'custom-file-input']) !!}
                    </div>
                    <div class="text-muted">Recommended size: {{ config('lorekeeper.settings.masterlist_thumbnails.width') }}px x {{ config('lorekeeper.settings.masterlist_thumbnails.height') }}px</div>
                </div>
            </div>
        @endif
        <p>
            This section is for crediting the image creators. The first box is for the designer or artist's on-site username (if any). The second is for a link to the designer or artist if they don't have an account on the site.
        </p>
        <div class="form-group">
            {!! Form::label('Designer(s)') !!}
            <div id="designerList">
                <?php $designerCount = count($request->designers); ?>
                @foreach ($request->designers as $count => $designer)
                    <div class="mb-2 d-flex">
                        {!! Form::select('designer_id[' . $designer->id . ']', $users, $designer->user_id, ['class' => 'form-control mr-2 selectize', 'placeholder' => 'Select a Designer']) !!}
                        {!! Form::text('designer_url[' . $designer->id . ']', $designer->url, ['class' => 'form-control mr-2', 'placeholder' => 'Designer URL']) !!}

                        <a href="#" class="add-designer btn btn-link" data-toggle="tooltip" title="Add another designer" @if ($count != $designerCount - 1) style="visibility: hidden;" @endif>+</a>
                    </div>
                @endforeach
                @if (!count($request->designers))
                    <div class="mb-2 d-flex">
                        {!! Form::select('designer_id[]', $users, null, ['class' => 'form-control mr-2 selectize', 'placeholder' => 'Select a Designer']) !!}
                        {!! Form::text('designer_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Designer URL']) !!}
                        <a href="#" class="add-designer btn btn-link" data-toggle="tooltip" title="Add another designer">+</a>
                    </div>
                @endif
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('Artist(s)') !!}
            <div id="artistList">
                <?php $artistCount = count($request->artists); ?>
                @foreach ($request->artists as $count => $artist)
                    <div class="mb-2 d-flex">
                        {!! Form::select('artist_id[' . $artist->id . ']', $users, $artist->user_id, ['class' => 'form-control mr-2 selectize', 'placeholder' => 'Select an Artist']) !!}
                        {!! Form::text('artist_url[' . $artist->id . ']', $artist->url, ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
                        <a href="#" class="add-artist btn btn-link" data-toggle="tooltip" title="Add another artist" @if ($count != $artistCount - 1) style="visibility: hidden;" @endif>+</a>
                    </div>
                @endforeach
                @if (!count($request->artists))
                    <div class="mb-2 d-flex">
                        {!! Form::select('artist_id[]', $users, null, ['class' => 'form-control mr-2 selectize', 'placeholder' => 'Select an Artist']) !!}
                        {!! Form::text('artist_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
                        <a href="#" class="add-artist btn btn-link" data-toggle="tooltip" title="Add another artist">+</a>
                    </div>
                @endif
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
        </div>

        {!! Form::close() !!}
    @endif


    <div class="designer-row hide mb-2">
        {!! Form::select('designer_id[]', $users, null, ['class' => 'form-control mr-2 designer-select', 'placeholder' => 'Select a Designer']) !!}
        {!! Form::text('designer_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Designer URL']) !!}
        <a href="#" class="add-designer btn btn-link" data-toggle="tooltip" title="Add another designer">+</a>
    </div>
    <div class="artist-row hide mb-2">
        {!! Form::select('artist_id[]', $users, null, ['class' => 'form-control mr-2 artist-select', 'placeholder' => 'Select an Artist']) !!}
        {!! Form::text('artist_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
        <a href="#" class="add-artist btn btn-link mb-2" data-toggle="tooltip" title="Add another artist">+</a>
    </div>

@endsection

@section('scripts')
    @include('widgets._image_upload_js', ['useUploaded' => $request->status == 'Pending' && Auth::user()->hasPower('manage_characters')])
    @if ($request->status == 'Pending' && Auth::user()->hasPower('manage_characters'))
        @include('widgets._design_update_measurements_js')
        @include('widgets._design_update_values_js')
        {{-- MODE TOGGLE --}}
        <script>
            $(function() {
                const $img = $('#request-image');
                const $svg = $('#measure-svg');
                const $canvas = $('#request-canvas');
                const canvas = $canvas[0];
                const ctx = canvas.getContext('2d');
                const $btnMeasure = $('#mode-measure');
                const $btnColour = $('#mode-colour');
                const $values = $('#values');

                function setActive($on, $off) {
                    $on.addClass('btn-primary active').removeClass('btn-outline-primary').attr('aria-pressed', 'true');
                    $off.removeClass('btn-primary active').addClass('btn-outline-primary').attr('aria-pressed', 'false');
                }

                function sizeSvgToImage() {
                    $svg.css({
                        position: 'absolute',
                        left: 0,
                        top: 0,
                        width: $img.width() + 'px',
                        height: $img.height() + 'px'
                    });
                }

                function ensureCanvasSizeAndDraw() {
                    const iwNat = $img[0].naturalWidth;
                    const ihNat = $img[0].naturalHeight;
                    if (!iwNat || !ihNat) {
                        return;
                    }
                    if (canvas.width !== iwNat || canvas.height !== ihNat) {
                        canvas.width = iwNat;
                        canvas.height = ihNat;
                    }

                    const iwCSS = $img.width();
                    const ihCSS = $img.height();
                    $canvas.css({
                        position: 'absolute',
                        left: 0,
                        top: 0,
                        width: iwCSS + 'px',
                        height: ihCSS + 'px',
                        zIndex: 3
                    });

                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage($img[0], 0, 0, canvas.width, canvas.height);
                }

                let mode = 'measure';

                function setMode(next) {
                    mode = next;
                    if (mode == 'measure') {
                        sizeSvgToImage();
                        $svg.show().css({
                            'pointer-events': 'auto',
                            zIndex: 4,
                            cursor: 'crosshair'
                        });
                        $canvas.hide().css({
                            'pointer-events': 'none',
                            zIndex: 3
                        });
                        setActive($btnMeasure, $btnColour);
                    } else {
                        ensureCanvasSizeAndDraw();
                        sizeSvgToImage();
                        $svg.show().css({
                            'pointer-events': 'none',
                            zIndex: 4
                        });
                        $canvas.show().css({
                            'pointer-events': 'auto',
                            zIndex: 3,
                            cursor: 'crosshair'
                        });
                        setActive($btnColour, $btnMeasure);
                    }
                }

                $btnMeasure.on('click', function() {
                    setMode('measure');
                });
                $btnColour.on('click', function() {
                    setMode('colour');
                });

                $img.on('load', () => {
                    if (mode !== 'measure') ensureCanvasSizeAndDraw();
                });
                $(window).on('resize', () => {
                    if ($canvas.is(':visible')) ensureCanvasSizeAndDraw();
                });

                setMode('measure');
            });
        </script>
    @endif
@endsection
