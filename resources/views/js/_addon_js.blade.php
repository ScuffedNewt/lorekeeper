<script>
$(document).ready(function() {
        $('.original.addon-select').selectize();
        $('#add-addon').on('click', function(e) {
            e.preventDefault();
            addFeatureRow();
        });
        $('.remove-addon').on('click', function(e) {
            e.preventDefault();
            removeFeatureRow($(this));
        })
        function addFeatureRow() {
            var $clone = $('.addon-row').clone();
            $('#addonList').append($clone);
            $clone.removeClass('hide addon-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-addon').on('click', function(e) {
                e.preventDefault();
                removeFeatureRow($(this));
            })
            $clone.find('.addon-select').selectize();
        }
        function removeFeatureRow($trigger) {
            $trigger.parent().remove();
        }
    });
</script>
