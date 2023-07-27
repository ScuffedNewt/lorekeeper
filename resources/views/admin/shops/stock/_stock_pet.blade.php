{!! Form::label('ID') !!}
{!! Form::select('item_id', $pets, $stock->item_id ?? null, ['class' => 'form-control selectize']) !!}

<script>
    $( document ).ready(function() {
        $('.selectize').selectize();
    });
</script>