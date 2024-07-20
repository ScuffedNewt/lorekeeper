@php
    $elements = \App\Models\Element\Element::orderBy('name')->pluck('name', 'id');
    // check if there is a type for this object if not passed
    if (!isset($type)) {
        $type = \App\Models\Element\Typing::where('typing_model', get_class($object))
            ->where('typing_id', $object->id)
            ->first();
    }
    $type = $type ?? null;
    $isStaff = isset($isStaff) ? $isStaff : true;
@endphp

<div class="card p-4 mb-2 mt-2" id="typing-card">
    <h3>Typings</h3>

    <p>You can add typings to this object by selecting an element from the dropdown below and clicking "Add Typing".
        <br><b>You can have a maximum of {{ config('lorekeeper.extensions.max_elements') }} typings on an object.</b>
    </p>
    {!! isset($info) ? '<p class="alert alert-info">' . $info . '</p>' : '' !!}

    <div class="typing">
        <div id="elements">
            @if ($type)
                <h5>Typing for {!! $type->object->displayName !!}</h5>
                Current Typing: {!! $type->elementNames !!}
                @foreach ($type->element_ids as $id)
                    <div class="form-group">
                        {!! Form::label('Element') !!}
                        {!! Form::select('element_ids[]', $elements, $id, ['class' => 'form-control typing-selectize', 'placeholder' => 'Select Element']) !!}
                    </div>
                @endforeach
            @endif
        </div>
        <div class="btn btn-secondary" id="add-element">Add Element</div>
        @if ($isStaff)
            <div class="btn btn-primary float-right" id="submit-typing">{{ $type ? 'Edit' : 'Create' }} Typing</div>
            @if ($type)
                <div class="btn btn-danger float-right mr-2" id="delete-typing">Delete Typing</div>
            @endif
        @endif
    </div>
</div>

<div class="form-group hide element-row">
    {!! Form::label('Element') !!}
    {!! Form::select('element_ids[]', $elements, null, ['class' => 'form-control select', 'placeholder' => 'Select Element']) !!}
</div>

<script>
    $(document).ready(function() {
        $('.typing-selectize').selectize();

        // add element
        $('#add-element').on('click', function(e) {
            e.preventDefault();
            // make sure there are less than 2 elements
            if ($('#elements').find('.form-group').length >= {{ config('lorekeeper.extensions.max_elements') }}) {
                return;
            }
            var $clone = $('.element-row').clone();
            $('#elements').append($clone);
            $clone.removeClass('hide element-row');
            $clone.find('select').selectize();
        });

        // delete typing
        @if ($type && $isStaff)
            $('#delete-typing').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/typing/delete/' . $type->id) }}", "Delete Typing");
            });
        @endif

        @if ($isStaff)
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
                        type: '{{ $type ? $type->id : null }}',
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
        @endif
    });
</script>
