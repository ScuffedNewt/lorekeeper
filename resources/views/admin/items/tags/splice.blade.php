{!! Form::label('Select Specific Variants') !!}
<p>If you want this splice item to only apply specific variants from each pet, select them here. Otherwise, leave this blank to be able to apply any variant.</p>
{!! Form::select('variant_ids[]', ['default' => 'Default'] + $variants, isset($tag->getData()['variant_ids']) ? $tag->getData()['variant_ids'] : null, ['class' => 'form-control selectize', 'multiple', 'placeholder' => 'Select Variants']) !!}

<script>
    $(document).ready(function() {
        $('.selectize').selectize({
            maxItems: 20
        });
    });
</script>
