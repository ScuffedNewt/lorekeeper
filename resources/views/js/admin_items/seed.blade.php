@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])

<script>
    $(document).ready(function() {
        var $gardenTable = $('#gardenTableBody');
        var $gardenRow = $('#gardenRow').find('.garden-row');
        var $gardenItemSelect = $('#gardenRowData').find('.item-select');
        var $gardenCurrencySelect = $('#gardenRowData').find('.currency-select');
        var $gardenTableSelect = $('#gardenRowData').find('.table-select');
        var $gardenRaffleSelect = $('#gardenRowData').find('.raffle-select');

        $('#gardenTableBody .selectize').selectize();
        attachRemoveListener($('#gardenTableBody .remove-garden-button'));

        $('#addGarden').on('click', function(e) {
            e.preventDefault();
            var $clone = $gardenRow.clone();
            $gardenTable.append($clone);
            attachRewardTypeListener($clone.find('.reward-type'));
            attachRemoveListener($clone.find('.remove-garden-button'));
        });

        $('.reward-type').on('change', function(e) {
            var val = $(this).val();
            var $cell = $(this).parent().parent().find('.garden-row-select');

            var $clone = null;
            if (val == 'Item') $clone = $gardenItemSelect.clone();
            else if (val == 'Currency') $clone = $gardenCurrencySelect.clone();
            else if (val == 'LootTable') $clone = $gardenTableSelect.clone();
            else if (val == 'Raffle') $clone = $gardenRaffleSelect.clone();

            $cell.html('');
            $cell.append($clone);
        });

        function attachRewardTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.garden-row-select');

                var $clone = null;
                if (val == 'Item') $clone = $gardenItemSelect.clone();
                else if (val == 'Currency') $clone = $gardenCurrencySelect.clone();
                else if (val == 'LootTable') $clone = $gardenTableSelect.clone();
                else if (val == 'Raffle') $clone = $gardenRaffleSelect.clone();

                $cell.html('');
                $cell.append($clone);
                $clone.selectize();
            });
        }

        function attachRemoveListener(node) {
            node.on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });
        }

    });
</script>
