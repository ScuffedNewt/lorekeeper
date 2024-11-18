<script>
    let selected_stack;
    let current_garden;
    let stack_type;
    let object_type;
    $('.garden-button').click(function(e) {
        e.preventDefault();
        $('.garden-button').removeClass('active').removeClass('focus');
        let id = $(this).data('garden-id');
        let current_garden_id = $(this).data('current-garden-id');
        let stack_type = $(this).data('stack-type');
        let object_type = $(this).data('object-type');
        selected_stack = id;
        current_garden = current_garden_id ?? 'none';
        stack_type = stack_type;
        object_type = object_type;

        console.log('Selected stack: ' + selected_stack);
        console.log('Current garden: ' + current_garden);
        console.log('Stack type: ' + stack_type);
        console.log('Object type: ' + object_type);

        $(this).addClass('focus');
        showGardenButtons(selected_stack);
    });

    $('.action-button').click(function(e) {
        e.preventDefault();
        let plot_id = $(this).data('plot-id');
        console.log('Selected stack: ' + selected_stack);
        console.log('Current garden: ' + current_garden);
        console.log('Plot ID: ' + plot_id);
        console.log('Stack type: ' + stack_type);
        console.log('Object type: ' + object_type);

        $.ajax({
            url: "{{ url('gardens/place') }}",
            type: 'POST',
            data: {
                'object_type': object_type,
                'stack_type': stack_type,
                'selected_stack_id': selected_stack,
                'current_garden_id': current_garden,
                'plot_id': plot_id,
                '_token': '{{ csrf_token() }}'
            },
            success: function(data) {
                console.log(data);
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error using item.');
                }
            },
            error: function(data) {
                console.log(data);
                alert('Error using item.');
            }
        });
    });

    function showGardenButtons(selected_stack) {
        if (selected_stack == 'deselect') {
            $('.action-button').addClass('hide');
            return;
        }
        $('.action-button').removeClass('hide');
    }
</script>
