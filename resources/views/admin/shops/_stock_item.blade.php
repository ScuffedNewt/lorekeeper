{!! Form::label('Select Stock:') !!}
{!! Form::select('item_id', $items, $stock->item_id ?? null, ['class' => 'form-control selectize']) !!}


<script>
    $( document ).ready(function() {
        $('.selectize').selectize();
    });
</script>