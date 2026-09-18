@php
    $elements = \App\Models\Element\Element::orderBy('name')->pluck('name', 'id');
    $typings = isset($typings) ? $typings : $object->typings;
@endphp

<div class="card p-4 mb-2 mt-2" id="typing-card">
    <h3>Typings</h3>

    <p>You can add typings to this object by selecting an element from the dropdown below and clicking "Add Typing".
        <br><b>You can have a maximum of 2 typings on an object.</b>
    </p>
    {!! isset($info) ? '<p class="alert alert-info">' . $info . '</p>' : '' !!}

    <div class="typing">
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h5 class="mb-0">Typing for {!! $object->displayName !!}</h5>
                Current Typing: {!! implode(', ', $typings->pluck('element')->pluck('displayName')->toArray()) !!}
            </div>
            <div class="text-right">
                <div class="btn btn-secondary" id="add-element">Add Element</div>
            </div>
        </div>
        <hr>
        <div id="elements">
            @if ($typings)
                @foreach ($typings as $typing)
                    <div class="row no-gutters">
                        <div class="col-11 form-group">
                            {!! Form::select('element_ids[]', $elements, $typing->element_id, ['class' => 'form-control element-selectize', 'placeholder' => 'Select Element']) !!}
                        </div>
                        <div class="col-1 pl-1 form-group text-center">
                            <div class="btn btn-danger remove-element mx-auto">X</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <hr>
        <div class="text-right">
            <div class="btn btn-primary" id="submit-typing">{{ $typings ? 'Edit' : 'Create' }} Typing</div>
            @if ($typings)
                <i class="fas fa-trash text-danger float-right mt-2 mx-2 fa-2x" data-toggle="tooltip" title="To delete typings, simply remove all existing typings and click 'Edit Typings'"></i>
            @endif
        </div>
    </div>
</div>

<div class="row no-gutters hide element-row">
    <div class="col-11 form-group">
        {!! Form::select('element_ids[]', $elements, null, ['class' => 'form-control select', 'placeholder' => 'Select Element']) !!}
    </div>
    <div class="col-1 pl-1 form-group text-center">
        <div class="btn btn-danger remove-element mx-auto">X</div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.element-selectize').selectize();

        // add element
        $('#add-element').on('click', function(e) {
            e.preventDefault();
            // make sure there are less than 2 elements
            if ($('#elements').find('select').length >= 2) {
                return;
            }
            var $clone = $('.element-row').clone();
            $('#elements').append($clone);
            $clone.removeClass('hide element-row');
            $clone.find('.select').selectize();
            attachRemoveListener($clone.find('.remove-element'));
        });

        $('.remove-element').each(function() {
            attachRemoveListener($(this));
        });

        function attachRemoveListener(node) {
            node.on('click', function(e) {
                e.preventDefault();
                $(this).closest('.row').remove();
            });
        }

        // ajax on add typing
        $('#submit-typing').on('click', function(e) {
            e.preventDefault();
            var $typing = $('.typing');
            var $submit = $typing.find('#submit-typing');
            var $error = $typing.find('.error');
            var $success = $typing.find('.success');

            $submit.addClass('disabled');
            $error.addClass('d-none');
            $success.addClass('d-none');

            $.ajax({
                url: "{{ url('admin/typing') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    typing_model: '{{ urlencode(get_class($object)) }}',
                    typing_id: '{{ $object->id }}',
                    element_ids: $('#elements').find('select').map(function() {
                        return $(this).val();
                    }).get()
                },
                success: function(data) {
                    console.log('success');
                    location.reload();
                },
                error: function(data) {
                    console.log('error');
                    location.reload();
                }
            });
        });
    });
</script>
