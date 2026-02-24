@php
    // map the keys and the 'name' value of config('lorekeeper.limits.limit_types')
    $limitTypes = collect(config('lorekeeper.limits.limit_types'))->map(function ($value, $key) {
        return $value['name'];
    });
    $limits = hasLimits($object) ? getLimits($object) : null;

    $prompts = \App\Models\Prompt\Prompt::orderBy('name')->pluck('name', 'id')->toArray();
    $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id')->toArray();
    $currencies = \App\Models\Currency\Currency::orderBy('name')->pluck('name', 'id')->toArray();
    $dynamics = \App\Models\Limit\DynamicLimit::orderBy('name')->pluck('name', 'id')->toArray();
    $characterLevels = \App\Models\Level\Level::ordered('Character')->pluck('name', 'id')->toArray();
    $userLevels = \App\Models\Level\Level::ordered('User')->pluck('name', 'id')->toArray();
    $stats = \App\Models\Stat\Stat::orderBy('name')->pluck('name', 'id')->toArray();
    $classes = \App\Models\Character\CharacterClass::orderBy('name')->pluck('name', 'id')->toArray();
    $elements = \App\Models\Element\Element::orderBy('name')->pluck('name', 'id')->toArray();

    if (!isset($showUnlocked)) {
        $showUnlocked = true;
    }

    // Hiding auto unlock options are good for cases where the user should not know the option exists for that object
    // Prompts are a good example--users shouldn't know they can auto-unlock prompts, as that would be confusing, since
    // the UI for the prompt interactions occurs on submission...
    // ex the limits are checked as part of submitting a prompt,
    // requiring the user to manually unlock the prompt prior, especially if the limits are needed for every prompt submission, would be problematic.
    // also as currently implemented having manual unlocking required would effectively prevent users from submitting the prompt
    // as a refinement, this could be changed to allow for manual unlocking under certain conditions
    if (!isset($hideAutoUnlock)) {
        $hideAutoUnlock = false;
    }
@endphp

<div class="card p-4 mb-3 mt-3" id="limit-card">
    <h3>{{ isset($customHeader) ? $customHeader : 'Limits' }}</h3>

    <p>
        You can add requirements to this object by clicking "Add Limit" & selecting a requirement from the dropdown below.
        <br />
        Requirements are used to determine if a specific action can be performed on an object.
        <br /><b>Note that the checks for requirements are automatic, but their usage needs to be defined in the code.</b>
        <br /><b>Dynamic limits are created in the admin panel, but execute their logic in the code.</b>
    </p>
    {!! isset($info) ? '<p class="alert alert-info">' . $info . '</p>' : '' !!}

    {!! Form::open(['url' => 'admin/limits']) !!}
    {!! Form::hidden('object_model', get_class($object)) !!}
    {!! Form::hidden('object_id', $object->id) !!}
    <div class="limit">
        <div id="limits">
            @if ($limits)
                <h5>Limits for {!! $limits->first()->object->displayName !!}</h5>
            @endif
            @if ($showUnlocked)
                <div class="row">
                    <div class="col-md form-group">
                        {!! Form::label('is_unlocked', 'Is Unlocked?', ['class' => 'form-label font-weight-bold']) !!}
                        <p>
                            If this is set to "No", the object will continue to be locked until all requirements are met, every time the user attempts to use or interact with it.
                            <br />
                            If this is set to "Yes", the object will be unlocked for the user to interact with indefinitely after the requirements are met once.
                            <br />
                            The "Yes" option is good for one-time unlocks such as shops, locations, certain prompts, etc.
                        </p>
                        {!! Form::select('is_unlocked', ['yes' => 'Yes', 'no' => 'No'], $limits?->first()->is_unlocked ? 'yes' : 'no', ['class' => 'form-control']) !!}
                    </div>
                    @if (!$hideAutoUnlock)
                        <div class="col-md form-group border-left">
                            {!! Form::label('is_auto_unlocked', 'Automatically Unlock?', ['class' => 'form-label font-weight-bold']) !!} {!! add_help("This only affects objects with 'Is Unlocked?' set to 'Yes'.") !!}
                            <p>
                                If this is set to "No", the user must manually unlock the object by interacting with it - ex. clicking on the "Unlock" button.
                            <div class="text-warning">
                                This will prevent the limits from being used as part of a series of actions, ex. prompt submissions.
                            </div>
                            <br />
                            If this is set to "Yes", the object will be automatically unlocked when the user attempts to access them - ex. when a user enters a shop.
                            <br />
                            This setting is good for preventing users from being debited before being certain they want to interact with the object.
                            <div class="text-danger">
                                This option is not suitable for objects that should have limits as part of an action workflow, ex. prompt submissions.
                            </div>
                            </p>
                            {!! Form::select('is_auto_unlocked', ['yes' => 'Yes', 'no' => 'No'], $limits?->first()->is_auto_unlocked ? 'yes' : 'no', ['class' => 'form-control']) !!}
                        </div>
                    @else
                        {!! Form::hidden('is_auto_unlocked', 'yes') !!}
                    @endif
                </div>
            @endif
            @if ($limits)
                @foreach ($limits as $limit)
                    <div class="row">
                        <div class="col-md-3 form-group">
                            {!! Form::label('Limit Type') !!}
                            {!! Form::select('limit_type[]', $limitTypes, $limit->limit_type, ['class' => 'form-control limit-selectize limit-type', 'placeholder' => 'Select Limit Type']) !!}
                        </div>
                        <div class="col-md-4 form-group limit-select">
                            {!! Form::label('limit_id', 'Limit') !!}
                            @if ($limit->limit_type == 'prompt')
                                {!! Form::select('limit_id[]', $prompts, $limit->limit_id, ['class' => 'form-control limit prompts', 'placeholder' => 'Select Limit']) !!}
                            @elseif ($limit->limit_type == 'item')
                                {!! Form::select('limit_id[]', $items, $limit->limit_id, ['class' => 'form-control limit items', 'placeholder' => 'Select Limit']) !!}
                            @elseif ($limit->limit_type == 'currency')
                                {!! Form::select('limit_id[]', $currencies, $limit->limit_id, ['class' => 'form-control limit currencies', 'placeholder' => 'Select Limit']) !!}
                            @elseif ($limit->limit_type == 'dynamic')
                                {!! Form::select('limit_id[]', $dynamics, $limit->limit_id, ['class' => 'form-control limit dynamics', 'placeholder' => 'Select Limit']) !!}
                            @elseif ($limit->limit_type == 'character_level')
                                {!! Form::select('limit_id[]', $characterLevels, $limit->limit_id, ['class' => 'form-control limit character-levels', 'placeholder' => 'Select Limit']) !!}
                            @elseif ($limit->limit_type == 'user_level')
                                {!! Form::select('limit_id[]', $userLevels, $limit->limit_id, ['class' => 'form-control limit user-levels', 'placeholder' => 'Select Limit']) !!}
                            @elseif ($limit->limit_type == 'stat')
                                {!! Form::select('limit_id[]', $stats, $limit->limit_id, ['class' => 'form-control limit stats', 'placeholder' => 'Select Limit']) !!}
                            @elseif ($limit->limit_type == 'class')
                                {!! Form::select('limit_id[]', $classes, $limit->limit_id, ['class' => 'form-control limit classes', 'placeholder' => 'Select Limit']) !!}
                            @elseif ($limit->limit_type == 'element')
                                {!! Form::select('limit_id[]', $elements, $limit->limit_id, ['class' => 'form-control limit elements', 'placeholder' => 'Select Limit']) !!}
                            @endif
                        </div>
                        <div class="col-md-4 quantity {{ in_array($limit->limit_type, ['dynamic', 'element', 'user_level', 'character_level']) ? 'hide' : '' }}">
                            <div class="form-group">
                                {!! Form::label('Quantity') !!}
                                {!! Form::number('quantity[]', $limit->quantity, ['class' => 'form-control', 'placeholder' => 'Enter Quantity', 'min' => 0, 'step' => 1]) !!}
                            </div>
                            <div class="form-group debit {{ in_array($limit->limit_type, ['currency', 'item', 'stat']) ? '' : 'hide' }}">
                                {!! Form::label('Debit') !!}
                                {!! Form::select('debit[]', ['yes' => 'Debit', 'no' => 'Don\'t Debit'], $limit->debit ? 'yes' : 'no', ['class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="col-md-{{ in_array($limit->limit_type, ['dynamic', 'element', 'user_level', 'character_level']) ? '5 d-flex align-items-center mt-2' : '1 mt-2 d-flex align-items-center' }}">
                            <div class="btn btn-danger remove-limit {{ in_array($limit->limit_type, ['dynamic', 'element']) ? '' : 'mx-auto' }}">X</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="btn btn-secondary" id="add-limit">Add Limit</div>
        @if ($limits)
            <i class="fas fa-trash text-danger float-right mt-2 mx-2 fa-2x" data-toggle="tooltip" title="To delete limits, simply remove all existing limits and click 'Edit Limits'"></i>
        @endif
        {!! Form::submit(($limits ? 'Edit' : 'Create') . ' Limits', ['class' => 'btn btn-primary float-right']) !!}
    </div>
    {!! Form::close() !!}
</div>

<div class="hide limit-row">
    <div class="row">
        <div class="col-md-3 form-group">
            {!! Form::label('Limit Type') !!}
            {!! Form::select('limit_type[]', $limitTypes, null, ['class' => 'form-control limit-selectize limit-type', 'placeholder' => 'Select Limit Type']) !!}
        </div>
        <div class="col-md-4 form-group limit-select">
        </div>
        <div class="col-md-4 quantity hide">
            <div class="form-group">
                {!! Form::label('Quantity') !!}
                {!! Form::number('quantity[]', 0, ['class' => 'form-control', 'placeholder' => 'Enter Quantity', 'min' => 0, 'step' => 1]) !!}
            </div>
            <div class="form-group hide debit">
                {!! Form::label('Debit') !!}
                {!! Form::select('debit[]', ['yes' => 'Debit', 'no' => 'Don\'t Debit'], 'no', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-1 d-flex align-items-center">
            <div class="btn btn-danger remove-limit mx-auto">X</div>
        </div>
    </div>
</div>

<div id="rows" class="hide">
    {!! Form::label('limit_ids', 'Limit', ['class' => 'limit-label']) !!}
    {!! Form::select('limit_id[]', $prompts, null, ['class' => 'form-control limit prompts', 'placeholder' => 'Select Limit']) !!}
    {!! Form::select('limit_id[]', $items, null, ['class' => 'form-control limit items', 'placeholder' => 'Select Limit']) !!}
    {!! Form::select('limit_id[]', $currencies, null, ['class' => 'form-control limit currencies', 'placeholder' => 'Select Limit']) !!}
    {!! Form::select('limit_id[]', $dynamics, null, ['class' => 'form-control limit dynamics', 'placeholder' => 'Select Limit']) !!}
    {!! Form::select('limit_id[]', $characterLevels, null, ['class' => 'form-control limit character-levels', 'placeholder' => 'Select Limit']) !!}
    {!! Form::select('limit_id[]', $userLevels, null, ['class' => 'form-control limit user-levels', 'placeholder' => 'Select Limit']) !!}
    {!! Form::select('limit_id[]', $stats, null, ['class' => 'form-control limit stats', 'placeholder' => 'Select Limit']) !!}
    {!! Form::select('limit_id[]', $classes, null, ['class' => 'form-control limit classes', 'placeholder' => 'Select Limit']) !!}
    {!! Form::select('limit_id[]', $elements, null, ['class' => 'form-control limit elements', 'placeholder' => 'Select Limit']) !!}
</div>

<script>
    $(document).ready(function() {
        let $limitLabel = $('#rows').find('.limit-label');
        let $promptSelect = $('#rows').find('.prompts');
        let $itemSelect = $('#rows').find('.items');
        let $currencySelect = $('#rows').find('.currencies');
        let $dynamicSelect = $('#rows').find('.dynamics');
        let $characterLevelSelect = $('#rows').find('.character-levels');
        let $userLevelSelect = $('#rows').find('.user-levels');
        let $statSelect = $('#rows').find('.stats');
        let $classSelect = $('#rows').find('.classes');
        let $elementSelect = $('#rows').find('.elements');

        $('.limits-selectize').selectize();

        $('#add-limit').on('click', function(e) {
            e.preventDefault();
            var $clone = $('.limit-row').clone();
            $('#limits').append($clone);
            $clone.removeClass('hide limit-row');
            $clone.find('select').selectize();
            attachRewardTypeListener($clone.find('.limit-type'));
            attachRemoveListener($clone.find('.remove-limit'));
        });

        $('.limit-type').on('change', function() {
            let val = $(this).val();
            let $limit = $(this).parent().parent().find('.limit-select');

            let $clone = null;
            if (val == 'prompt') $clone = $promptSelect.clone();
            else if (val == 'item') $clone = $itemSelect.clone();
            else if (val == 'currency') $clone = $currencySelect.clone();
            else if (val == 'dynamic') $clone = $dynamicSelect.clone();
            else if (val == 'character_level') $clone = $characterLevelSelect.clone();
            else if (val == 'user_level') $clone = $userLevelSelect.clone();
            else if (val == 'stat') $clone = $statSelect.clone();
            else if (val == 'class') $clone = $classSelect.clone();
            else if (val == 'element') $clone = $elementSelect.clone();

            $limit.html('');
            $limit.append($limitLabel.clone());
            $limit.append($clone);

            // remove hide on quantity
            $(this).parent().parent().find('.quantity').removeClass('hide');
            // remove hide on debit if type is currency or item, otherwise hide it
            if (val == 'currency' || val == 'item' || val == 'stat') {
                $(this).parent().parent().parent().find('.debit').removeClass('hide');
                $(this).parent().parent().parent().find('.quantity').removeClass('hide');
            } else {
                $(this).parent().parent().parent().find('.debit').addClass('hide');
                if (val == 'dynamic' || val == 'element' || val == 'user_level' || val == 'character_level') {
                    $(this).parent().parent().parent().find('.quantity').addClass('hide');
                } else {
                    $(this).parent().parent().parent().find('.quantity').removeClass('hide');
                }
            }
        });

        // attach remove listener to all .remove-limit
        $('.remove-limit').each(function() {
            attachRemoveListener($(this));
        });

        function attachRewardTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.limit-select');

                var $clone = null;
                if (val == 'prompt') $clone = $promptSelect.clone();
                else if (val == 'item') $clone = $itemSelect.clone();
                else if (val == 'currency') $clone = $currencySelect.clone();
                else if (val == 'dynamic') $clone = $dynamicSelect.clone();
                else if (val == 'character_level') $clone = $characterLevelSelect.clone();
                else if (val == 'user_level') $clone = $userLevelSelect.clone();
                else if (val == 'stat') $clone = $statSelect.clone();
                else if (val == 'class') $clone = $classSelect.clone();
                else if (val == 'element') $clone = $elementSelect.clone();

                $cell.html('');
                $cell.append($limitLabel.clone());
                $cell.append($clone);

                $(this).parent().parent().find('.quantity').removeClass('hide');
                if (val == 'currency' || val == 'item' || val == 'stat') {
                    $(this).parent().parent().find('.debit').removeClass('hide');
                    $(this).parent().parent().find('.quantity').removeClass('hide');
                } else {
                    $(this).parent().parent().find('.debit').addClass('hide');
                    if (val == 'dynamic' || val == 'element' || val == 'user_level' || val == 'character_level') {
                        $(this).parent().parent().find('.quantity').addClass('hide');
                    } else {
                        $(this).parent().parent().find('.quantity').removeClass('hide');
                    }
                }
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
