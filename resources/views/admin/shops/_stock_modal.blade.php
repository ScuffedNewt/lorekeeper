<div class="p-2">
    @if($stock->id)
    {!! Form::open(['url' => 'admin/data/shops/stock/edit/'.$stock->id]) !!}
    @else
    {!! Form::open(['url' => 'admin/data/shops/stock/'.$shop->id]) !!}
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('stock_type', 'Type') !!}
                {!! Form::select('stock_type', ['Item' => 'Item', 'Pet' => 'Pet'], $stock->stock_type ?? null, ['class' => 'form-control stock-field', 'placeholder' => 'Select Stock Type', 'id' => 'type']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group" id="stock">
                @if($stock->id)
                    @include('admin.shops._stock_item', ['items' => $items, 'stock' => $stock])
                @endif
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('cost', 'Cost') !!}
        <div class="row">
            <div class="col-4">
                {!! Form::text('cost', $stock->cost ?? null, ['class' => 'form-control stock-field', 'data-name' => 'cost']) !!}
            </div>
            <div class="col-8">
                {!! Form::select('currency_id', $currencies, $stock->currency_id ?? null, ['class' => 'form-control stock-field', 'data-name' => 'currency_id']) !!}
            </div>
        </div>
    </div>
    <div class="pl-4">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::checkbox('use_user_bank', 1, $stock->use_user_bank ?? 1, ['class' => 'form-check-input stock-toggle stock-field', 'data-name' => 'use_user_bank']) !!}
                    {!! Form::label('use_user_bank', 'Use User Bank', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will allow users to purchase the item using the currency in their accounts, provided that users can own that currency.') !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-0">
                    {!! Form::checkbox('use_character_bank', 1, $stock->use_character_bank ?? 1, ['class' => 'form-check-input stock-toggle stock-field', 'data-name' => 'use_character_bank']) !!}
                    {!! Form::label('use_character_bank', 'Use Character Bank', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will allow users to purchase the item using the currency belonging to characters they own, provided that characters can own that currency.') !!}
                </div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::checkbox('is_fto', 1, $stock->is_fto ?? 0, ['class' => 'form-check-input stock-toggle stock-field', 'data-name' => 'is_fto']) !!}
            {!! Form::label('is_fto', 'FTO Only?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, only FTO will be able to purchase the item.') !!}
        </div>

        <div class="form-group">
            {!! Form::checkbox('is_limited_stock', 1, $stock->is_limited_stock ?? 0, ['class' => 'form-check-input stock-limited stock-toggle stock-field', 'id' => 'is_limited_stock']) !!}
            {!! Form::label('is_limited_stock', 'Set Limited Stock', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, will limit the amount purchaseable to the quantity set below.') !!}
        </div>

        <div class="form-group">
            {!! Form::checkbox('disallow_transfer', 1, $stock->disallow_transfer ?? 0, ['class' => 'form-check-input stock-toggle stock-field', 'data-name' => 'disallow_transfer']) !!}
            {!! Form::label('disallow_transfer', 'Disallow Transfer', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, users will be unable to transfer this item after purchase.') !!}
        </div>

        <div class="form-group">
            {!! Form::checkbox('is_visible', 1, $stock->is_visible ?? 1, ['class' => 'form-check-input stock-limited stock-toggle stock-field']) !!}
            {!! Form::label('is_visible', 'Set Visibility', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off it will not appear in the store.') !!}
        </div>
    </div>

    <div class="card mb-3 stock-limited-quantity {{ $stock->is_limited_stock ? '' : 'hide' }}">
        <div class="card-body">
            <div>
                {!! Form::label('quantity', 'Quantity') !!} {!! add_help('If left blank, will be set to 0 (sold out).') !!}
                {!! Form::text('quantity', $stock->quantity ?? 0, ['class' => 'form-control stock-field']) !!}
            </div>
            <div class="my-2">
                {!! Form::checkbox('restock', 1, $stock->restock ?? 0, ['class' => 'form-check-input']) !!}
                {!! Form::label('restock', 'Auto Restock?',  ['class' => 'form-check-label ml-3']) !!} {!! add_help('If ticked to yes it will auto restock at the interval defined below.') !!}
            </div>
            <div>
                {!! Form::label('restock_interval', 'Restock Interval') !!}
                {!! Form::select('restock_interval', [1 => 'Day', 2 => 'Week', 3 => 'Month'], $stock->restock_interval ?? 2, ['class' => 'form-control stock-field']) !!}
            </div>
            <div class="my-2">
                {!! Form::checkbox('range', 1, $stock->range ?? 0, ['class' => 'form-check-input']) !!}
                {!! Form::label('range', 'Restock in Range?',  ['class' => 'form-check-label ml-3']) !!} {!! add_help('If ticked to yes it will restock a random quantity between 1 and the quantity set above.') !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            {!! Form::label('purchase_limit', 'User Purchase Limit') !!} {!! add_help('This is the maximum amount of this item a user can purchase from this shop. Set to 0 to allow infinite purchases.') !!}
            {!! Form::text('purchase_limit', $stock ? $stock->purchase_limit : 0, ['class' => 'form-control stock-field', 'data-name' => 'purchase_limit']) !!}
        </div>
        <div class="col-md-6">
            {!! Form::label('purchase_limit_timeframe', 'Purchase Limit Timeout') !!} {!! add_help('This is the timeframe that the purchase limit will apply to. I.E. yearly will only look at purchases made after the beginning of the current year. Weekly starts on Sunday. Rollover will happen on UTC time.') !!}
            {!! Form::select('purchase_limit_timeframe', ["lifetime" => "Lifetime", "yearly" => "Yearly", "monthly" => "Monthly", "weekly" => "Weekly", "daily" => "Daily"] , $stock ? $stock->purchase_limit_timeframe : 0, ['class' => 'form-control stock-field', 'data-name' => 'purchase_limit_timeframe']) !!}
        </div>
    </div>
<br>
<div class="pl-4">
    <div class="form-group">
            {!! Form::checkbox('is_timed_stock', 1, $stock->is_timed_stock ?? 0, ['class' => 'form-check-input stock-timed stock-toggle stock-field', 'id' => 'is_timed_stock']) !!}
            {!! Form::label('is_timed_stock', 'Set Timed Stock', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Sets the stock as timed between the chosen dates.') !!}
        </div>
    <div class="stock-timed-quantity {{ $stock->is_timed_stock ? '' : 'hide' }}">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('start_at', 'Start Time') !!} {!! add_help('Stock will cycle in at this date.') !!}
                    {!! Form::text('start_at', $stock->start_at, ['class' => 'form-control datepicker']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('end_at', 'End Time') !!} {!! add_help('Stock will cycle out at this date.') !!}
                    {!! Form::text('end_at', $stock->end_at, ['class' => 'form-control datepicker']) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="text-right mt-1">
    {!! Form::submit($stock->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}
</div>
<script>
    $(document).ready(function() {
        $('#type').change(function() {
            var type = $(this).val();
            $.ajax({
            type: "GET", url: "{{ url('admin/data/shops/stock-type') }}?type="+type, dataType: "text"
            }).done(function (res) { $("#stock").html(res); }).fail(function (jqXHR, textStatus, errorThrown) { alert("AJAX call failed: " + textStatus + ", " + errorThrown); });
        });

        // is_limited_stock change
        $('#is_limited_stock').change(function() {
            if ($(this).is(':checked')) {
                $('.stock-limited-quantity').removeClass('hide');
            }
            else {
                $('.stock-limited-quantity').addClass('hide');
            }
        });
        // is_timed_stock change
        $('#is_timed_stock').change(function() {
            if ($(this).is(':checked')) {
                $('.stock-timed-quantity').removeClass('hide');
            }
            else {
                $('.stock-timed-quantity').addClass('hide');
            }
        });

        $(".datepicker").datetimepicker({
            dateFormat: "yy-mm-dd",
            timeFormat: 'HH:mm:ss',
            beforeShow: function (input, inst) {
                const box = inst.input[0].getBoundingClientRect();
                setTimeout(function () {
                    inst.dpDiv.css({
                        top: box.top - inst.dpDiv[0].offsetHeight,
                        left: box.left
                    });
                }, 0);
            }
        });
    });
</script>

