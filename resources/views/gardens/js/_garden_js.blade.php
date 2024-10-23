<script>
    let selected_stack;
    let current_garden;
    $('.garden-button').click(function(e) {
        e.preventDefault();
        $('.garden-button').removeClass('active').removeClass('focus');
        let id = $(this).data('garden-id');
        let current_garden_id = $(this).data('current-garden-id');
        selected_stack = id;
        current_garden = current_garden_id ?? 'none';

        $(this).addClass('focus');
        console.log('Selected stack: ' + selected_stack);
        console.log('Current garden: ' + current_garden);

        showGardenButtons(selected_stack);
    });

    $('.action-button').click(function(e) {
        e.preventDefault();
        let action = $(this).data('action');
        console.log('Action: ' + action);
        console.log('Selected stack: ' + selected_stack);
        console.log('Current garden: ' + current_garden);

        // if (action == 'move') {
        //     if (current_garden == 'none') {
        //         alert('Please select a garden to move this item to.');
        //         return;
        //     }
        //     $.ajax({
        //         url: "{{ url('gardens/move') }}",
        //         type: 'POST',
        //         data: {
        //             'selected_stack': selected_stack,
        //             'current_garden': current_garden,
        //             '_token': '{{ csrf_token() }}'
        //         },
        //         success: function(data) {
        //             console.log(data);
        //             if (data.success) {
        //                 alert('Item moved successfully.');
        //                 location.reload();
        //             } else {
        //                 alert('Error moving item.');
        //             }
        //         },
        //         error: function(data) {
        //             console.log(data);
        //             alert('Error moving item.');
        //         }
        //     });
        // }
    });

    function showGardenButtons(selected_stack) {
        if (selected_stack == 'deselect') {
            $('.action-button').addClass('hide');
            return;
        }
        $('.action-button').removeClass('hide');
    }
</script>
