<script>
    let current_garden;
    let stack_type;
    let stack_id;
    let object_type;
    $('.garden-button').click(function(e) {
        e.preventDefault();
        $('.garden-button').removeClass('active').removeClass('focus');
        let id = $(this).data('garden-id');
        let current_garden_id = $(this).data('current-garden-id');
        let stackType = $(this).data('stack-type');
        let objectType = $(this).data('object-type');
        stack_id = id;
        current_garden = current_garden_id ?? 'none';
        stack_type = stackType;
        object_type = objectType;

        $(this).addClass('focus');
        showGardenButtons();
    });

    $('.action-button').click(function(e) {
        e.preventDefault();
        let plot_id = $(this).data('plot-id');

        $.ajax({
            url: "{{ url('gardens/place') }}",
            type: 'POST',
            data: {
                'object_type': object_type,
                'stack_type': stack_type,
                'stack_id': stack_id,
                'current_garden_id': current_garden,
                'plot_id': plot_id,
                '_token': '{{ csrf_token() }}'
            },
            complete: function(response) {
                location.reload();
            }
        });
    });

    function showGardenButtons() {
        if (stack_id == 'deselect') {
            // hide all action buttons
            $('.action-button').each(function() {
                $(this).addClass('hide');
            });
            return;
        }

        // unhide all action buttons
        $('.action-button').each(function() {
            $(this).removeClass('hide');
        });
    }
</script>
