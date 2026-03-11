<script>
    const toolSlots = $('.slot.tool');
    const seedSlots = $('.slot.seed');
    const plots = $('.plot');
    const statusElement = $('#status');
    const ghost = $('#ghost');
    const modeButtons = $('[data-mode]');
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

        smoothTextChange(modeHint, modeConfig[mode].banner);
        toolbarHint.text(modeConfig[mode].toolbar);
        statusElement.text(`Mode: ${capitalize(mode)}. Selected tool: ${selectedTool}`);
    }

    function capitalize(value) {
        return value.charAt(0).toUpperCase() + value.slice(1);
    }

    function updatePlotUI(plot, icon, text, state) {
        const $plot = $(plot);

        $plot.attr('data-state', state);
        $plot.toggleClass('planted', state !== 'empty');

        $plot.html(`
            <div class="plot-label">
                ${String($plot.data('plotId')).replace('plot-', 'Plot ')}
            </div>
            <div class="plot-center">
                <span class="icon">${icon}</span>
                ${text}
            </div>
        `);
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
        statusElement.text(`Mode: ${capitalize(currentMode)}. Selected tool: ${selectedTool}`);
    });

    modeButtons.on('click', function() {
        setMode($(this).data('mode'));
    });

    seedSlots.on('dragstart', function(e) {
        if (currentMode !== 'place') {
            e.preventDefault();
            statusElement.text(`Mode: ${capitalize(currentMode)}. Switch to Place mode to drag seeds.`);
            return;
        }

        const $seed = $(this);
        draggedItem = {
            id: $seed.data('id'),
            name: $seed.data('name'),
            icon: $seed.data('icon') ? `<img src='${$seed.data('icon')}' style='max-height: 30px;'>` : null,
            currentGardenId: $seed.data('currentGardenId'),
            assetType: $seed.data('assetType'),
            objectType: $seed.data('objectType'),
            element: $seed
        };

        $seed.addClass('dragging');
        if (draggedItem.icon) {
            ghost.html(draggedItem.icon).show();
        } else {
            ghost.text('🌱').show();
        }
        e.originalEvent.dataTransfer.setData('text/plain', draggedItem.name);
        e.originalEvent.dataTransfer.setDragImage(emptyDragImage, 0, 0);
    });
    seedSlots.on('dragend', function() {
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

            if (currentMode !== 'place') {
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

            if (currentMode !== 'place') {
                return;
            }

            $plot.removeClass('drop-hover');

            if (!draggedItem) {
                return;
            }

            // optimistic update
            updatePlotUI($plot, draggedItem.icon, `${draggedItem.name} planted`, 'planted');
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
            if (currentMode === 'action') {
                statusElement.text(`Watering ${plotId.replace('plot-', 'Plot ')}...`);

                try {
                    let response = await sendGardenAction('care', {
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

            if (currentMode === 'collect') {
                statusElement.text(`Collecting ${plotId.replace('plot-', 'Plot ')}...`);

                try {
                    await sendGardenAction('collect', {
                        plotId: plotId
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
