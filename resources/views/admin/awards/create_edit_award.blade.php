@extends('admin.layout')

@section('admin-title')
    Awards
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Awards' => 'admin/data/awards',
        $award->id ? 'Edit ' . $award->name : 'Create Award' => $award->id ? 'admin/data/awards/edit/' . $award->id : 'admin/data/awards/create',
    ]) !!}

    <h1>{!! $award->id ? 'Edit ' . $award->displayName : 'Create Award' !!}
        @if ($award->id)
            <a href="#" class="btn btn-outline-danger float-right delete-award-button">Delete Award</a>
        @endif
    </h1>

    {!! Form::open(['url' => $award->id ? 'admin/data/awards/edit/' . $award->id : 'admin/data/awards/create', 'files' => true]) !!}

    <div class="card mb-3">
        <div class="card-header h3">
            Basic Information
        </div>
        <div class="card-body">
            <div class="row no-gutters">
                <div class="col-md-6 no-gutters">
                    <div class="form-group d-flex align-items-center">
                        {!! Form::label('name', 'Name', ['class' => 'col-3 mr-2 mb-0 font-weight-bold']) !!}
                        {!! Form::text('name', $award->id ? $award->name : Request::get('name'), ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group d-flex align-items-center">
                        {!! Form::label('award_category_id', 'Category (Optional)', ['class' => 'col-3 mr-2 mb-0 font-weight-bold']) !!}
                        {!! Form::select('award_category_id', $categories, $award->award_category_id, ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group d-flex align-items-center">
                        {!! Form::label('rarity_id', 'Rarity (Optional)', ['class' => 'col-3 mr-2 mb-0 font-weight-bold']) !!}
                        {!! Form::select('rarity_id', $rarities, $award && $award->rarity_id ? $award->rarity_id : null, ['class' => 'form-control', 'placeholder' => 'Select Rarity']) !!}
                    </div>
                </div>
                <div class="form-group col-md-6 pl-md-3 d-flex flex-row align-items-center justify-content-md-center">
                    @if ($award->has_image)
                        <div>
                            <img src="{{ $award->imageUrl }}" class="img-fluid float-left mr-2" style="max-width: 100px;" />
                        </div>
                    @endif
                    <div>
                        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
                        <div class="custom-file">
                            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
                            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
                        </div>
                        <div class="text-muted">Recommended size: 100px x 100px</div>
                        @if ($award->has_image)
                            <div class="form-check">
                                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header h3">
            Settings
        </div>
        <div class="card-body">
            <div class="row no-gutters">
                <div class="col-md-4 form-group pl-md-3">
                    {!! Form::checkbox('is_released', 1, $award->id ? $award->is_released : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('is_released', 'Is Released', ['class' => 'form-check-label font-weight-bold ml-3']) !!}
                    {!! add_help('If this is off, users will not be able to view information for the award/it will be hidden from view. This is overridden by the Award being owned at any point by anyone on the site.') !!}
                </div>
                <div class="col-md-4 form-group pl-md-3">
                    {!! Form::checkbox('is_featured', 1, $award->id ? $award->is_featured : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('is_featured', 'Is Featured', ['class' => 'form-check-label font-weight-bold ml-3']) !!}
                    {!! add_help('This award is featured on the holder\'s profile. <br>Limited to the number set in extension configuration per character/user.') !!}
                </div>
                <div class="col-md-4 form-group pl-md-3">
                    {!! Form::checkbox('allow_transfer', 1, $award->id ? $award->allow_transfer : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('allow_transfer', 'Allow User → User Transfer', ['class' => 'form-check-label font-weight-bold ml-3']) !!} {!! add_help('If this is off, users will not be able to transfer this award to other users. Non-account-bound awards can be account-bound when granted to users directly.') !!}
                </div>
                <div class="col-md-6 form-group pl-md-3">
                    {!! Form::checkbox('is_character_owned', 1, $award->id ? $award->is_character_owned : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('is_character_owned', 'Character Held', ['class' => 'form-check-label font-weight-bold ml-3']) !!}
                    {!! add_help('If this is enabled, characters will be able to hold this award. The limit is how many can be held at a time. 0 means no limit, if set to 1 then quantity will be treated as a boolean.') !!}
                    <div class="limit d-inline-flex align-items-center ml-3 ">
                        {!! Form::label('character_limit', 'Character Hold Limit', ['class' => 'font-weight-bold mr-3 mb-0']) !!}
                        {!! Form::number('character_limit', $award->id ? $award->character_limit : 0, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-6 form-group pl-md-3">
                    {!! Form::checkbox('is_user_owned', 1, $award->id ? $award->is_user_owned : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('is_user_owned', 'User Held', ['class' => 'form-check-label font-weight-bold ml-3']) !!}
                    {!! add_help('If this is enabled, users will be able to hold this award. The limit is how many can be held at a time. 0 means no limit, if set to 1 then quantity will be treated as a boolean.') !!}
                    <div class="limit d-inline-flex align-items-center ml-3 ">
                        {!! Form::label('user_limit', 'User Hold Limit', ['class' => 'font-weight-bold mr-3 mb-0']) !!}
                        {!! Form::number('user_limit', $award->id ? $award->user_limit : 0, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header h3">
            Description (Optional)
        </div>
        <div class="card-body">
            <div class="form-group">
                {!! Form::textarea('description', $award->description, ['class' => 'form-control wysiwyg']) !!}
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="mb-0">Availability Information</h3>
        </div>
        <div class="card-body">
            <div class="row mb-0">
                <div class="col">
                    <div class="form-group d-md-flex align-items-center">
                        {!! Form::label('prompts[]', 'Prompts (Optional)', ['class' => 'mr-2 mb-0 font-weight-bold']) !!} {!! add_help('You can select up to 10 prompts at once.') !!}
                        {!! Form::select('prompts[]', $prompts, $award && isset($award->data['prompts']) ? $award->data['prompts'] : '', ['id' => 'promptsList', 'class' => 'ml-md-2 form-control', 'multiple']) !!}
                    </div>
                </div>

                <div class="col form-group d-md-flex align-items-center">
                    {!! Form::label('release', 'Source (Optional)', ['class' => 'mr-2 mb-0 font-weight-bold']) !!} {!! add_help('Where or how this award was earned.') !!}
                    {!! Form::text('release', $award && $award->source ? $award->source : '', ['class' => 'ml-md-2 form-control']) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="mb-0">
                <a href="#" class="btn btn-primary mr-2 float-right btn-sm" id="add-credit-button">Add Credit</a> Credits
            </h3>
        </div>
        <div class="card-body">
            <div class="row no-gutters form-group" id="creditList" style="clear:both;">
                @if ($award->id && isset($award->credits))
                    @foreach ($award->credits as $id => $credit)
                        <div class="col-md-3 align-items-center mb-2">
                            <a href="#" class="remove-credit-button btn btn-danger btn-sm mr-2"><i class="fas fa-trash"></i></a>
                            <a href="{{ isset($credit['url']) ? $credit['url'] : (isset($credit['id']) ? url('/user') . '/' . $userOptions[$credit['id']] : 'unknown') }}" target="_blank">
                                {{ isset($credit['name']) ? $credit['name'] : (isset($credit['id']) ? $userOptions[$credit['id']] : (isset($credit['url']) ? $credit['url'] : 'artist')) }}
                            </a>
                            {{ isset($credit['role']) ? '(' . $credit['role'] . ')' : '' }}
                            @foreach ($credit as $type => $info)
                                {!! Form::hidden('credit-' . $type . '[' . $id . ']', $info) !!}
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    @if ($award->id)
        <div class="card mb-3">
            <div class="card-header h3">
                Automatic Unlock Progression
            </div>
            <div class="card-body">
                <p>
                    If you want this award to be automatically unlocked by acquiring certain items, currencies, etc. add them here.
                    <br />
                    When a user has owned (at one point) all of these items, the award will be distributed to them on their next visit to the awards page
                    <hr />
                    If the progressions are changed after a user has claimed the award, they will see the requirements they fulfilled alongside the current ones.
                </p>
                <hr />
                @include('widgets._add_rewards', [
                    'object' => $award,
                    'hideTitle' => true,
                    'useForm' => false,
                    'showRaffles' => true,
                    'showLootTables' => true,
                    'prefix' => 'progression_',
                    'type' => 'Progression',
                    'loots' => $award->progressions,
                ])
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="mb-0">Unlock Reward</h3>
            </div>
            <div class="card-body">
                <p>
                    If you want this award to grant a reward when claimed, add it here.
                    <br />
                    When a user receives an award through obtaining all of the above items, they will also receive this reward.
                </p>
                <p>
                    <b>The user will only receive this reward through the automatic unlock process.
                        When they claim the award, if the award has any rewards all the items required for unlock will be debitted from
                        their inventory.
                    </b>
                </p>
                <div class="row">
                    <div class="col-md-6 form-group">
                        {!! Form::checkbox('debit_progressions', 1, $award->id ? $award->debit_progressions : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                        {!! Form::label('debit_progressions', 'Debit Progressions?', ['class' => 'form-check-label font-weight-bold ml-3']) !!}
                        {!! add_help('Do you want the user to have the items required for unlock taken from their inventory when they receive the award? This only applies to receiving the award through the automatic unlock process, not when claimed through prompts or grants.') !!}
                    </div>
                    <div class="col-md-6 form-group">
                        {!! Form::checkbox('allow_reclaim', 1, $award->id ? $award->allow_reclaim : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                        {!! Form::label('allow_reclaim', 'Allow Reclaim?', ['class' => 'form-check-label font-weight-bold ml-3']) !!}
                        {!! add_help('Do you want the user to be able to claim the reward from this badge multiple times? They will only receive the badge once.') !!}
                    </div>
                </div>
                @include('widgets._add_rewards', [
                    'object' => $award,
                    'hideTitle' => true,
                    'useForm' => false,
                    'showRaffles' => true,
                    'showLootTables' => true,
                ])
            </div>
        </div>
    @endif

    <div class="text-right mt-2">
        {!! Form::submit($award->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary px-5']) !!}
    </div>

    {!! Form::close() !!}

    <div class="row hide credit-row col-12 mb-1">
        <div class="col-md-2">
            {!! Form::select('credit-type[]', ['onsite' => 'Onsite Credit', 'offsite' => 'Offsite Credit'], null, ['class' => 'form-control mr-2 credit-type']) !!}
        </div>
        <div class="col-md-6 credit-info d-flex">
            {!! Form::select('credit-id[]', $userOptions, null, ['class' => 'form-control credit-select', 'placeholder' => 'Select a User']) !!}
            {!! Form::hidden('credit-name[]', null) !!}
            {!! Form::hidden('credit-url[]', null) !!}
        </div>
        <div class="col-md">
            {!! Form::text('credit-role[]', null, ['class' => 'ml-md-2 form-control', 'placeholder' => 'Credit Role (Optional)']) !!}
        </div>
        <a href="#" class="col-md-auto remove-credit-button btn btn-danger" style="height:fit-content;"><i class="fas fa-trash"></i></a>
    </div>

    <div id="credit-info-onsite" class="hide credit-info ">
        {!! Form::select('credit-id[]', $userOptions, null, ['class' => 'form-control mr-2 credit-select', 'placeholder' => 'Select a User']) !!}
        {!! Form::hidden('credit-name[]', null) !!}
        {!! Form::hidden('credit-url[]', null) !!}
    </div>

    <div id="credit-info-offsite" class="hide credit-info ">
        {!! Form::text('credit-name[]', null, ['class' => 'form-control col', 'placeholder' => 'Name']) !!}
        {!! Form::text('credit-url[]', null, ['class' => 'form-control col ml-1', 'placeholder' => 'Url']) !!}
        {!! Form::hidden('credit-id[]', null) !!}
    </div>


    @if ($award->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._award_entry', [
                    'award' => $award,
                    'staffView' => true,
                ])
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @include('js._tinymce_wysiwyg')
    @parent
    <script>
        $(document).ready(function() {
            var $credits = $('#creditsTable');
            var $value = 1000;

            $('#promptsList').selectize({
                maxAwards: 10
            });

            $('.delete-award-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/awards/delete') }}/{{ $award->id }}", 'Delete Award');
            });

            $('#add-credit-button').on('click', function(e) {
                e.preventDefault();
                addCreditRow();
            });
            $('.remove-credit-button').on('click', function(e) {
                e.preventDefault();
                removeCreditRow($(this));
            })

            function addCreditRow() {
                var $clone = $('.credit-row').clone();
                $('#creditList').append($clone);
                $clone.removeClass('hide credit-row');
                $clone.attr('name', $value++);
                $clone.find('.remove-credit-button').on('click', function(e) {
                    e.preventDefault();
                    removeCreditRow($(this));
                })
                $clone.find('.credit-type').on('change', function(e) {
                    $val = $clone.find('.credit-type').val();
                    if ($val == "onsite") {
                        addOnsite($clone.find('.credit-info'));
                        $clone.find('.credit-select').selectize();
                    } else if ($val == "offsite") addOffsite($clone.find('.credit-info'));
                });
                $clone.find('.credit-select').selectize();
            }

            function removeCreditRow($trigger) {
                $trigger.parent().remove();
            }


            function addOnsite($info) {
                $clone = $('#credit-info-onsite').children().clone();
                $clone.removeClass('hide').addClass('col-md-12');
                $info.html($clone);
            }

            function addOffsite($info) {
                $clone = $('#credit-info-offsite').children().clone();
                $clone.removeClass('hide').addClass('col-md');
                $info.html($clone);
            }

        });
    </script>
@endsection
