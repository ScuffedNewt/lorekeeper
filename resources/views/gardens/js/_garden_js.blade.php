<script>
    const toolSlots = $('.slot.tool');
    const assetSlots = $('.slot.asset');
    const plots = $('.plot');
    const statusElement = $('.garden-status');
    const ghost = $('#ghost');
    ghost.hide();
    const modeButtons = $('.mode-pills label[data-mode]');
    const modeHint = $('#modeHint');
    const toolbarHint = $('#toolbarHint');
    const emptyDragImage = new Image();

    let selectedTool = 'None';
    let draggedItem = null;
    let selectedPlaceable = null;
    let currentMode = modeButtons.filter('.active').data('mode') || 'place';
    emptyDragImage.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxIiBoZWlnaHQ9IjEiPjwvc3ZnPg==';

    function mixColour(colour, target, amount) {
        return colour.map((channel, index) => Math.round(channel + (target[index] - channel) * amount));
    }

    function colourDistance(first, second) {
        return Math.sqrt(first.reduce((total, channel, index) => total + Math.pow(channel - second[index], 2), 0));
    }

    function cssColour(colour) {
        return `rgb(${colour.join(', ')})`;
    }

    const gardenPaletteProperties = [
        '--garden-green', '--garden-green-dark', '--garden-green-bright', '--garden-pale',
        '--garden-cream', '--garden-tan', '--garden-surface', '--garden-border',
        '--garden-soil', '--garden-soil-dark'
    ];

    function applyGardenPalette(element, palette) {
        gardenPaletteProperties.forEach(property => {
            if (palette && palette[property]) {
                element.style.setProperty(property, palette[property]);
            } else {
                element.style.removeProperty(property);
            }
        });
    }

    function activateGardenPalette(tabPane) {
        const card = tabPane ? tabPane.closest('.garden-card') : null;
        const scene = tabPane ? tabPane.querySelector('.scene') : null;
        if (card) applyGardenPalette(card, scene ? scene.gardenPalette : null);
    }

    function sampleGardenPalette(scene) {
        const source = scene.dataset.paletteSource;
        if (!source) return;

        const image = new Image();
        image.onload = function() {
            try {
                const canvas = document.createElement('canvas');
                canvas.width = 48;
                canvas.height = 48;
                const context = canvas.getContext('2d', {
                    willReadFrequently: true
                });
                context.drawImage(image, 0, 0, canvas.width, canvas.height);

                const pixels = context.getImageData(0, 0, canvas.width, canvas.height).data;
                const buckets = new Map();
                for (let index = 0; index < pixels.length; index += 4) {
                    if (pixels[index + 3] < 128) continue;
                    const colour = [pixels[index], pixels[index + 1], pixels[index + 2]];
                    const brightness = colour.reduce((sum, channel) => sum + channel, 0) / 3;
                    if (brightness < 20 || brightness > 242) continue;

                    const key = colour.map(channel => Math.floor(channel / 32) * 32 + 16).join(',');
                    buckets.set(key, (buckets.get(key) || 0) + 1);
                }

                const colours = Array.from(buckets.entries())
                    .sort((first, second) => second[1] - first[1])
                    .map(entry => entry[0].split(',').map(Number));
                if (!colours.length) return;

                const primary = colours[0];
                const secondary = colours.find(colour => colourDistance(primary, colour) > 80) || primary;
                scene.gardenPalette = {
                    '--garden-green': cssColour(primary),
                    '--garden-green-dark': cssColour(mixColour(primary, [0, 0, 0], .32)),
                    '--garden-green-bright': cssColour(mixColour(primary, [255, 255, 255], .18)),
                    '--garden-pale': cssColour(mixColour(primary, [255, 255, 255], .82)),
                    '--garden-cream': cssColour(mixColour(primary, [255, 255, 255], .92)),
                    '--garden-tan': cssColour(mixColour(secondary, [255, 255, 255], .72)),
                    '--garden-surface': cssColour(mixColour(primary, [255, 255, 255], .96)),
                    '--garden-border': cssColour(mixColour(secondary, [255, 255, 255], .52)),
                    '--garden-soil': cssColour(mixColour(secondary, [0, 0, 0], .2)),
                    '--garden-soil-dark': cssColour(mixColour(secondary, [0, 0, 0], .4))
                };
                applyGardenPalette(scene, scene.gardenPalette);
                if (scene.closest('.tab-pane')?.classList.contains('active')) {
                    activateGardenPalette(scene.closest('.tab-pane'));
                }
            } catch (error) {
                console.warn('The garden background palette could not be sampled.', error);
            }
        };
        image.src = source;
    }

    document.querySelectorAll('.scene[data-palette-source]').forEach(sampleGardenPalette);
    $('#gardenTabs a[data-toggle="tab"]').on('shown.bs.tab', function() {
        activateGardenPalette(document.querySelector($(this).attr('href')));
    });


    const modeConfig = {
        place: {
            banner: 'Place mode: drag placeable objects onto plots',
            toolbar: 'Tools are click-to-equip. Placeable objects are drag-to-place.'
        },
        tend: {
            banner: 'Tend mode: click a planted plot to water it, or an animal\'s plot to feed it',
            toolbar: 'Use Tend mode to look after crops and animals on your plots.'
        },
        collect: {
            banner: 'Collect mode: click a ready plot to collect it',
            toolbar: 'Collect mode is for clicking in-use plots, not dragging seeds or animals.'
        }
    };

    function smoothTextChange($el, text) {
        $el.addClass('squish');
        setTimeout(() => {
            $el.text(text);
            $el.removeClass('squish');
        }, 80);
    }

    function setMode(mode) {
        currentMode = mode;

        modeButtons.each(function() {
            const $button = $(this);
            $button.toggleClass('active', $button.data('mode') === mode);
        });

        plots.removeClass('highlight');
        if (mode === 'tend') {
            plots.filter('.tend').addClass('highlight');
        } else if (mode === 'collect') {
            plots.filter('.collect').addClass('highlight');
        }

        smoothTextChange(modeHint, modeConfig[mode].banner);
        toolbarHint.text(modeConfig[mode].toolbar);
        tools = toolSlots.filter(`[data-mode="${mode}"]`);
        tools.addClass('active');
        if (tools.length) {
            selectedTool = tools.data('name');
            statusElement.text(`Mode: ${capitalize(mode)}. Selected tool: ${selectedTool}`);
        } else {
            selectedTool = capitalize(mode);
        }
    }

    function capitalize(value) {
        return value.charAt(0).toUpperCase() + value.slice(1);
    }

    function updatePlotUI(plot, icon, text, state) {
        const $plot = $(plot);

        $plot.attr('data-state', state);
        $plot.toggleClass('full', state !== 'empty');
        $plot.toggleClass('empty', state === 'empty');

        let currentPlotLabel = $plot.find('.plot-label');

        $plot.html(`
            ${currentPlotLabel.prop('outerHTML')}
            <div class="plot-center">
                <span class="icon">${icon}</span>
                ${text}
            </div>
        `);

        // todo, fetch instead of just updating the UI - this will ensure the plot state is accurate and also update timers and other dynamic elements
    }

    async function sendGardenAction(action, payload) {
        return $.ajax({
            url: "{{ url('gardens/action') }}",
            type: 'POST',
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            data: JSON.stringify({
                action,
                ...payload
            }),
        });
    }

    $(document).on('dragover', function(e) {
        if (ghost.is(':visible')) {
            ghost.css({
                left: `${e.clientX}px`,
                top: `${e.clientY}px`
            });
        }
    });

    toolSlots.on('click', function() {
        toolSlots.removeClass('active');
        $(this).addClass('active');
        selectedTool = $(this).data('name');
        setMode($(this).data('mode'));
        statusElement.text(`Mode: ${capitalize(currentMode)}. Selected tool: ${selectedTool}`);
    });

    modeButtons.on('click', function() {
        toolSlots.removeClass('active');
        setMode($(this).data('mode'));
    });

    assetSlots.on('dragstart', function(e) {
        if (currentMode !== 'place') {
            e.preventDefault();
            statusElement.text(`Mode: ${capitalize(currentMode)}. Switch to Place mode to drag assets.`);
            return;
        }

        const $asset = $(this);
        draggedItem = {
            id: $asset.data('id'),
            name: $asset.data('name'),
            icon: $asset.data('icon') ? `<img src='${$asset.data('icon')}' class='garden-object-image'>` : null,
            currentGardenId: $asset.data('currentGardenId'),
            assetType: $asset.data('assetType'),
            objectType: $asset.data('objectType'),
            element: $asset
        };

        $asset.addClass('dragging');
        if (draggedItem.icon) {
            ghost.html(draggedItem.icon).show();
        } else {
            ghost.html('<i class="fas fa-seedling" aria-hidden="true"></i>').show();
        }
        e.originalEvent.dataTransfer.setData('text/plain', draggedItem.name);
        e.originalEvent.dataTransfer.setDragImage(emptyDragImage, 0, 0);
    });
    assetSlots.on('click', function(e) {
        e.preventDefault();
        const $asset = $(this);
        assetSlots.removeClass('active');
        $asset.addClass('active');
        selectedPlaceable = $asset;
        draggedItem = {
            id: $asset.data('id'),
            name: $asset.data('name'),
            icon: $asset.data('icon') ? `<img src='${$asset.data('icon')}' class='garden-object-image'>` : null,
            currentGardenId: $asset.data('currentGardenId'),
            assetType: $asset.data('assetType'),
            objectType: $asset.data('objectType'),
            element: $asset
        };
        setMode('place');
        statusElement.text(`Selected ${draggedItem.name}. Click or tap an empty plot to place it.`);
    });
    assetSlots.on('dragend', function() {
        $(this).removeClass('dragging');
        ghost.hide();
        plots.removeClass('drop-hover');
    });

    plots.each(function() {
        const $plot = $(this);
        const plotId = String($plot.data('plotId') || '');

        if (plotId.startsWith('unowned')) {
            return;
        }

        $plot.on('dragover', function(e) {
            e.preventDefault();

            if (currentMode !== 'place' || !$plot.hasClass('empty')) {
                return;
            }

            $plot.addClass('drop-hover');
            $plot.find('.plot-center .icon').addClass('bounce')
        });

        $plot.on('dragleave', function() {
            $plot.removeClass('drop-hover');
            $plot.find('.plot-center .icon').removeClass('bounce');
        });

        $plot.on('drop', async function(e) {
            e.preventDefault();

            if (currentMode !== 'place' || !$plot.hasClass('empty')) {
                return;
            }

            $plot.removeClass('drop-hover');

            if (!draggedItem) {
                return;
            }

            // optimistic update
            updatePlotUI($plot, draggedItem.icon, `${draggedItem.name}`, 'full');
            // debit the draggedItem on the UI immediately to provide feedback that the item has been used - if the request fails, we will refund it in the catch block
            if (draggedItem.element) {
                const currentQuantity = parseInt(draggedItem.element.data('quantity'), 10);
                console.log(currentQuantity)
                let newQuantity = currentQuantity - 1;
                if (!newQuantity) {
                    newQuantity = 0;
                }
                draggedItem.element.data('quantity', newQuantity);
                draggedItem.element.find('.quantity').text('x' + newQuantity);
            }
            statusElement.text(`Placing ${draggedItem.name} on ${plotId.replace('plot-', 'Plot ')}...`);

            try {
                let response = await sendGardenAction('place', {
                    plot_id: plotId,
                    asset_id: draggedItem.id,
                    asset_type: draggedItem.assetType,
                    object_type: draggedItem.objectType,
                    current_garden_id: draggedItem.currentGardenId,
                });

                console.log('Garden action response:', response);

                if (!response.success) {
                    if (response.errors) {
                        console.error('Garden action validation errors:', response.errors);
                    }
                    throw new Error(response.message || 'Request failed.');
                }

                statusElement.text(response.message || `Placed ${draggedItem.name} on ${plotId.replace('plot-', 'Plot ')}.`);
                // completely update plot UI with response from server to ensure accuracy (timers, etc)
                applyPlotResponse($plot, response);
            } catch (error) {
                updatePlotUI($plot, '<i class="fas fa-plus" aria-hidden="true"></i>', 'Empty plot', 'empty');
                statusElement.text(error.message || `Could not place ${draggedItem.name}. Please try again.`);
                console.error('Garden action failed:', error);
            }
        });

        $plot.on('click', async function(e) {
            if (currentMode === 'place' && $plot.hasClass('empty') && draggedItem && !$plot.data('readOnly')) {
                $plot.trigger($.Event('drop'));
                return;
            }
            if ($plot.data('readOnly') && $plot.hasClass('tend')) {
                const $guestStatus = $plot.closest('.scene').find('.garden-status');
                try {
                    const response = await $.ajax({
                        url: "{{ url('gardens/guest-tend') }}",
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        data: {
                            plot_id: plotId
                        }
                    });
                    $guestStatus.text(response.message);
                    if (response.remaining !== null) {
                        $('.guest-waters-remaining').text(response.remaining);
                    }
                    await refreshPlot($plot);
                    if (response.remaining === 0) {
                        plots.filter('[data-read-only="1"]').removeClass('tend highlight');
                        $guestStatus.removeClass('alert-info').addClass('alert-warning');
                        $('.guest-watering-alert').removeClass('alert-info').addClass('alert-warning');
                    }
                } catch (error) {
                    $guestStatus.text(error.responseJSON?.message || 'Could not water this plant.');
                }
                return;
            }
            if (currentMode === 'tend') {
                if (!$plot.hasClass('tend')) {
                    return;
                }
                statusElement.text(`Watering ${plotId.replace('plot-', 'Plot ')}...`);

                try {
                    let response = await sendGardenAction('tend', {
                        plot_id: plotId
                    });

                    if (!response.success) {
                        throw new Error(response.message || 'Request failed.');
                    }

                    statusElement.text(`Watered ${plotId.replace('plot-', 'Plot ')}.`);
                    applyPlotResponse($plot, response);
                } catch (error) {
                    statusElement.text(error.message || `Could not water ${plotId.replace('plot-', 'Plot ')}.`);
                    console.error('Garden action failed:', error);
                }

                return;
            }

            console.log('Plot clicked:', plotId, 'Current mode:', currentMode);
            if (currentMode === 'collect') {
                if (!$plot.hasClass('collect')) {
                    return;
                }
                const collectionOutcome = $(e.target).closest('[data-collection-outcome]').data('collectionOutcome');
                if ($plot.find('.collect-outcome').length && !collectionOutcome) {
                    statusElement.text('Choose whether this animal should stay or be removed.');
                    return;
                }
                statusElement.text(`Collecting ${plotId.replace('plot-', 'Plot ')}...`);

                try {
                    const response = await sendGardenAction('collect', {
                        plot_id: plotId,
                        collection_outcome: collectionOutcome,
                    });

                    if (!response.success) {
                        throw new Error(response.message || 'Request failed.');
                    }

                    statusElement.html(response.message || `Collected ${plotId.replace('plot-', 'Plot ')}.`);
                    applyPlotResponse($plot, response);
                } catch (error) {
                    statusElement.text(`Could not collect ${plotId.replace('plot-', 'Plot ')}.`);
                    console.error('Garden action failed:', error);
                }
            }
        });
    });

    function applyPlotResponse($plot, response) {
        $plot.find('.plot-center').html(response.view || '<span class="icon"><i class="fas fa-plus" aria-hidden="true"></i></span>Empty plot');
        $plot.toggleClass('empty', response.state === 'empty');
        $plot.toggleClass('full', response.state !== 'empty');
        $plot.toggleClass('tend', !!response.actionReady);
        $plot.toggleClass('collect', !!response.canCollect);
        $('[data-toggle="tooltip"]').tooltip({
            html: true
        });
    }

    async function refreshPlot($plot) {
        const id = String($plot.data('plotId') || '');
        if (!/^\d+$/.test(id)) return;
        const response = await $.get(`{{ url('gardens/plot') }}/${id}`);
        applyPlotResponse($plot, response);
    }

    $(document).on('click', '.remove-animal', async function(e) {
        e.stopPropagation();
        if (!confirm('Return this animal to your available inventory without collecting rewards?')) return;
        const $plot = $(this).closest('.plot');
        const response = await sendGardenAction('remove_animal', {
            plot_id: $(this).data('plotId')
        });
        if (response.success) applyPlotResponse($plot, response);
    });

    function updateCountdowns() {
        const now = Math.floor(Date.now() / 1000);
        $('[data-countdown], [data-ready-countdown]').each(function() {
            const $el = $(this);
            const target = Number($el.data('countdown') || $el.data('readyCountdown'));
            const seconds = Math.max(0, target - now);
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            const value = `${hours}h ${minutes}m ${secs}s`;
            if ($el.is('[data-ready-countdown]')) $el.text(`Ready in ${value}`);
            else $el.text(seconds ? `in ${value}` : ($el.data('countdownLabel') || 'Ready'));
        });
    }
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
    setInterval(() => plots.each(function() {
        refreshPlot($(this));
    }), 30000);

    setMode(currentMode);
</script>
