@extends('admin.layout')

@section('admin-title')
    Grant Stat Points
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Grant Stat Points' => 'admin/grants/stat-points']) !!}

    <h1>Grant Stat Points</h1>
    <p>Specific stat points can be only be granted to characters. If a user is selected while specific stat points are selected, the grant will be ignored.</p>
    <p>E.g. If you select a user and then select "Health", the user will receive no points.</p>

    {!! Form::open(['url' => 'admin/grants/stat-points']) !!}

    <h3>Basic Information</h3>
    <div class="form-group">
        {!! Form::label('names[]', 'Username(s) / Slug(s)') !!} {!! add_help('You can select up to 10 users at once.') !!}
        {!! Form::select('names[]', $options, null, ['id' => 'usernameList', 'class' => 'form-control', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Stat Points(s)') !!} {!! add_help('Must have at least 1 Stat and Quantity must be at least 1.') !!}
        <div id="itemList">
            <div class="d-flex mb-2">
                {!! Form::select('stat_ids[]', $stats, null, ['class' => 'form-control mr-2 default item-select', 'placeholder' => 'Select Stat']) !!}
                {!! Form::text('quantity[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
                <a href="#" class="remove-item btn btn-danger mb-2 disabled">×</a>
            </div>
        </div>
        <div><a href="#" class="btn btn-primary" id="add-item">Add Stat</a></div>
    </div>

    <h3>Additional Data</h3>

    <div class="form-group">
        {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the logs and in the inventory description.') !!}
        {!! Form::text('data', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
    </div>

    <div class="text-right">
        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <div class="item-row hide mb-2">
        {!! Form::select('stat_ids[]', $stats, null, ['class' => 'form-control mr-2 item-select', 'placeholder' => 'Select Item']) !!}
        {!! Form::text('quantity[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
        <a href="#" class="remove-item btn btn-danger mb-2">×</a>
    </div>

    <script>
        $(document).ready(function() {
            $('#usernameList').selectize({
                maxItems: 10
            });
            $('.default.item-select').selectize();
            $('#add-item').on('click', function(e) {
                e.preventDefault();
                addItemRow();
            });
            $('.remove-item').on('click', function(e) {
                e.preventDefault();
                removeItemRow($(this));
            })

            function addItemRow() {
                var $rows = $("#itemList > div")
                if ($rows.length === 1) {
                    $rows.find('.remove-item').removeClass('disabled')
                }
                var $clone = $('.item-row').clone();
                $('#itemList').append($clone);
                $clone.removeClass('hide item-row');
                $clone.addClass('d-flex');
                $clone.find('.remove-item').on('click', function(e) {
                    e.preventDefault();
                    removeItemRow($(this));
                })
                $clone.find('.item-select').selectize();
            }

            function removeItemRow($trigger) {
                $trigger.parent().remove();
                var $rows = $("#itemList > div")
                if ($rows.length === 1) {
                    $rows.find('.remove-item').addClass('disabled')
                }
            }
        });
    </script>
@endsection
