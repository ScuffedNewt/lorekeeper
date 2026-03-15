<script>
    const toolSlots = $('.slot.tool');
    const assetSlots = $('.slot.asset');
    const plots = $('.plot');
    const statusElement = $('#status');
    const ghost = $('#ghost');
    const modeButtons = $('.mode-pills label[data-mode]');
    const modeHint = $('#modeHint');
    const toolbarHint = $('#toolbarHint');
    const emptyDragImage = new Image();

    let selectedTool = 'None';
    let draggedItem = null;
    let currentMode = 'place';
    emptyDragImage.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxIiBoZWlnaHQ9IjEiPjwvc3ZnPg==';


    const modeConfig = {
        place: {
            banner: 'Place mode: drag seeds and animals onto plots',
            toolbar: 'Tools are click-to-equip. Seeds and animals are drag-to-place.'
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
        selectedTool = tools.data('name');
        statusElement.text(`Mode: ${capitalize(mode)}. Selected tool: ${selectedTool}`);
    }

    function capitalize(value) {
        return value.charAt(0).toUpperCase() + value.slice(1);
    }

    function updatePlotUI(plot, icon, text, state) {
        const $plot = $(plot);

        $plot.attr('data-state', state);
        $plot.toggleClass('full', state !== 'empty');

        $plot.html(`
            <div class="plot-label">
                ${String($plot.data('plotId')).replace('plot-', 'Plot ')}
            </div>
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
            icon: $asset.data('icon') ? `<img src='${$asset.data('icon')}' style='max-height: 30px;'>` : null,
            currentGardenId: $asset.data('currentGardenId'),
            assetType: $asset.data('assetType'),
            objectType: $asset.data('objectType'),
            element: $asset
        };

        $asset.addClass('dragging');
        if (draggedItem.icon) {
            ghost.html(draggedItem.icon).show();
        } else {
            ghost.text('🌱').show();
        }
        e.originalEvent.dataTransfer.setData('text/plain', draggedItem.name);
        e.originalEvent.dataTransfer.setDragImage(emptyDragImage, 0, 0);
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
            updatePlotUI($plot, draggedItem.icon, `${draggedItem.name} planted`, 'full');
            // debit the draggedItem on the UI immediately to provide feedback that the item has been used - if the request fails, we will refund it in the catch block
            if (draggedItem.element) {
                const currentQuantity = parseInt(draggedItem.element.data('quantity'), 10);
                console.log(currentQuantity)
                draggedItem.element.data('quantity', currentQuantity - 1);
                draggedItem.element.find('.quantity').text('x' + currentQuantity - 1);
            }
            statusElement.text(`Planting ${draggedItem.name} on ${plotId.replace('plot-', 'Plot ')}...`);

            try {
                let response = await sendGardenAction('place', {
                    plot_id: plotId,
                    asset_id: draggedItem.id,
                    asset_type: draggedItem.assetType,
                    object_type: draggedItem.objectType,
                    current_garden_id: draggedItem.currentGardenId,
                });

                if (!response.success) {
                    if (response.errors) {
                        console.error('Garden action validation errors:', response.errors);
                    }
                    throw new Error(response.message || 'Request failed.');
                }

                statusElement.text(`Planted ${draggedItem.name} on ${plotId.replace('plot-', 'Plot ')}.`);
            } catch (error) {
                updatePlotUI($plot, '🌱', 'Empty soil', 'empty');
                statusElement.text(`Could not plant ${draggedItem.name}. Please try again.`);
                console.error('Garden action failed:', error);
            }
        });

        $plot.on('click', async function() {
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
                } catch (error) {
                    statusElement.text(`Could not water ${plotId.replace('plot-', 'Plot ')}.`);
                    console.error('Garden action failed:', error);
                }

                return;
            }

            console.log('Plot clicked:', plotId, 'Current mode:', currentMode);
            if (currentMode === 'collect') {
                if (!$plot.hasClass('collect')) {
                    return;
                }
                statusElement.text(`Collecting ${plotId.replace('plot-', 'Plot ')}...`);

                try {
                    await sendGardenAction('collect', {
                        plot_id: plotId,
                    });

                    statusElement.text(`Collected ${plotId.replace('plot-', 'Plot ')}.`);
                } catch (error) {
                    statusElement.text(`Could not collect ${plotId.replace('plot-', 'Plot ')}.`);
                    console.error('Garden action failed:', error);
                }
            }
        });
    });

    setMode('place');
</script>
