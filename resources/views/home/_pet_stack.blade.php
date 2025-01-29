@if (!$stack)
    <div class="text-center">Invalid pet selected.</div>
@else
    <div class="text-center">
        <div class="mb-1">
            <a href="{{ $stack->pet->url }}">
                <img class="img-fluid" src="{{ $stack->pet->image($stack->id) }}" />
            </a>
        </div>
        <div class="mb-1"><a href="{{ $stack->pet->url }}">{{ $stack->pet->name }}</a></div>
    </div>

    @if (isset($stack->data['notes']) || isset($stack->data['data']))
        <div class="card mt-3">
            <ul class="list-group list-group-flush">
                @if (isset($stack->data['notes']))
                    <li class="list-group-item">
                        <h5 class="card-title">Notes</h5>
                        <div>{!! $stack->data['notes'] !!}</div>
                    </li>
                @endif
                @if (isset($stack->data['data']))
                    <li class="list-group-item">
                        <h5 class="card-title">Source</h5>
                        <div>{!! $stack->data['data'] !!}</div>
                    </li>
                @endif
            </ul>
        </div>
    @endif

    <a class="btn btn-primary btn-lg btn-block h5 mt-3" href="{{ $stack->pageUrl(Auth::check() ? Auth::user()->id : null) }}">View Page</a>

    @if ($user && !$readOnly && ($stack->user_id == $user->id || $user->hasPower('edit_inventories')))
        <div class="card mt-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <a class="card-title h5 collapse-title" data-toggle="collapse" href="#nameForm">
                        @if ($stack->user_id != $user->id)
                            [ADMIN]
                        @endif Name Pet
                    </a>
                    {!! Form::open(['url' => 'pets/name/' . $stack->id, 'id' => 'nameForm', 'class' => 'collapse']) !!}
                    <p>Enter a name to display for the pet!</p>
                    <div class="form-group">
                        {!! Form::label('name', 'Name') !!} {!! add_help('If your name is not appropriate you can be banned.') !!}
                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="text-right">
                        {!! Form::submit('Name', ['class' => 'btn btn-primary']) !!}
                    </div>
                    {!! Form::close() !!}
                </li>
                <li class="list-group-item">
                    <a class="card-title h5 collapse-title" data-toggle="collapse" href="#descForm">
                        @if ($stack->user_id != Auth::user()->id)
                            [ADMIN]
                        @endif Edit Profile
                    </a>
                    {!! Form::open(['url' => 'pets/description/' . $stack->id, 'id' => 'descForm', 'class' => 'collapse']) !!}
                    <p>Tell everyone about your pet.</p>
                    <div class="form-group">
                        {!! Form::label('Profile Text (Optional)') !!}
                        {!! Form::textarea('description', $stack->description, ['class' => 'form-control wysiwyg']) !!}
                    </div>
                    <div class="text-right">
                        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                    </div>
                    {!! Form::close() !!}
                </li>
                <li class="list-group-item">
                    @php
                        $now = Carbon\Carbon::parse($stack->attached_at);
                        $diff = $now->addDays(Settings::get('claymore_cooldown'));
                    @endphp
                    @if ($stack->character_id != null && $diff < Carbon\Carbon::now())
                        <a class="card-title h5 collapse-title" data-toggle="collapse" href="#attachForm">
                            @if ($stack->user_id != $user->id)
                                [ADMIN]
                            @endif Detach Pet from Character
                        </a>
                        {!! Form::open(['url' => 'pets/detach/' . $stack->id, 'id' => 'attachForm', 'class' => 'collapse']) !!}
                        <p>This pet is currently attached to {!! $stack->character->displayName !!}, do you want to detach them?</p>
                        <div class="text-right">
                            {!! Form::submit('Detach', ['class' => 'btn btn-primary']) !!}
                        </div>
                        {!! Form::close() !!}
                    @elseif($stack->character_id == null || $diff < Carbon\Carbon::now())
                        <a class="card-title h5 collapse-title" data-toggle="collapse" href="#attachForm">
                            @if ($stack->user_id != $user->id)
                                [ADMIN]
                            @endif Attach Pet to Character
                        </a>
                        {!! Form::open(['url' => 'pets/attach/' . $stack->id, 'id' => 'attachForm', 'class' => 'collapse']) !!}
                        <p>Attach this pet to a character you own! They'll appear on the character's page and any stat bonuses will automatically be applied.</p>
                        <p>Pets can be detached.</p>
                        <div class="form-group">
                            {!! Form::label('id', 'Slug') !!} {!! add_help('Insert your character\'s slug.') !!}
                            {!! Form::select('id', $chara, null, ['class' => 'form-control']) !!}
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Attach', ['class' => 'btn btn-primary']) !!}
                        </div>
                        {!! Form::close() !!}
                    @else
                        <a class="card-title h5">You cannot currently attach / detach this pet! It is under cooldown.</a>
                    @endif
                </li>
                @if ($user && count($splices) && $user->id == $stack->user_id)
                    <li class="list-group-item">
                        <a class="card-title h5 collapse-title" data-toggle="collapse" href="#userVariantForm">Change Pet Variant</a>
                        {!! Form::open(['url' => 'pets/variant/' . $stack->id, 'id' => 'userVariantForm', 'class' => 'collapse']) !!}
                        <p>
                            This will use a splice item!
                            @if ($stack->pet->isVariant)
                                <br><b>Current variant:</b> {{ $stack->pet->name }}
                            @endif
                        </p>
                        <div class="form-group">
                            {!! Form::select('stack_id', $splices, null, ['class' => 'form-control', 'placeholder' => 'Select Item']) !!}
                        </div>
                        <div class="form-group">
                            @php
                                $variants = ['0' => 'Default'] + ($stack->pet->isVariant ? $stack->pet->parent->variants()->pluck('name', 'id')->toArray() : $stack->pet->variants()->pluck('name', 'id')->toArray());
                            @endphp
                            {!! Form::select('variant_id', $variants, $stack->pet->parent_id, ['class' => 'form-control']) !!}
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Change Variant', ['class' => 'btn btn-primary']) !!}
                        </div>
                        {!! Form::close() !!}
                    </li>
                @endif
                @if ($user->hasPower('edit_inventories'))
                    {{-- TODO --}}
                    {{-- variant --}}
                    <li class="list-group-item">
                        <a class="card-title h5 collapse-title" data-toggle="collapse" href="#variantForm">[ADMIN] Change Pet Variant</a>
                        {!! Form::open(['url' => 'pets/variant/' . $stack->id, 'id' => 'variantForm', 'class' => 'collapse']) !!}
                        {!! Form::hidden('is_staff', 1) !!}
                        <p>
                            @if ($stack->variant_id)
                                <br><b>Current variant:</b> {{ $stack->variant->name }}
                            @endif
                        </p>
                        <div class="form-group">
                            @php
                                $variants = ['0' => 'Default'] + ($stack->pet->isVariant ? $stack->pet->parent->variants()->pluck('name', 'id')->toArray() : $stack->pet->variants()->pluck('name', 'id')->toArray());
                            @endphp
                            {!! Form::select('variant_id', $variants, $stack->pet->isVariant ? $stack->pet_id : 0, ['class' => 'form-control mt-2']) !!}
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Change Variant', ['class' => 'btn btn-primary']) !!}
                        </div>
                        {!! Form::close() !!}
                    </li>
                    {{-- evolution --}}
                    <li class="list-group-item">
                        <a class="card-title h5 collapse-title" data-toggle="collapse" href="#evolutionForm">[ADMIN] Change Pet Evolution</a>
                        {!! Form::open(['url' => 'pets/evolution/' . $stack->id, 'id' => 'evolutionForm', 'class' => 'collapse']) !!}
                        {!! Form::hidden('is_staff', 1) !!}
                        <p>
                            @if ($stack->evolution_id)
                                <br><b>Current evolution:</b> {{ $stack->evolution->evolution_name }} (Stage {{ $stack->evolution->evolution_stage }})
                            @endif
                        </p>
                        <div class="form-group">
                            @php
                                $evolutions = ['0' => 'Default'] + $stack->pet->evolutions()->pluck('evolution_name', 'id')->toArray();
                            @endphp
                            {!! Form::select('evolution_id', $evolutions, $stack->evolution_id, ['class' => 'form-control mt-2']) !!}
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Change Evolution', ['class' => 'btn btn-primary']) !!}
                        </div>
                        {!! Form::close() !!}
                    </li>
                    {{-- custom pet image --}}
                    <li class="list-group-item">
                        <a class="card-title h5 collapse-title" data-toggle="collapse" href="#imageForm">[ADMIN] Change Image</a>
                        {!! Form::open(['url' => 'pets/image/' . $stack->id, 'id' => 'imageForm', 'class' => 'collapse', 'files' => true]) !!}
                        <div class="form-group">
                            {!! Form::label('Image') !!}
                            <div>{!! Form::file('image') !!}</div>
                            <div class="text-muted">Recommended size: 100px x 100px</div>
                            @if ($stack->has_image)
                                <div class="form-check">
                                    {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                                    {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                                </div>
                            @endif
                        </div>
                        <div class="col-md">
                            {!! Form::label('Pet Artist (Optional)') !!} {!! add_help('Provide the artist\'s username if they are on site or, failing that, a link.') !!}
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-group">
                                        {!! Form::select('artist_id', $userCreditOptions, $stack->artist_id ? $stack->artist_id : null, ['class' => 'form-control mr-2 selectize']) !!}
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-group">
                                        {!! Form::text('artist_url', $stack->artist_url ? $stack->artist_url : '', ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
                                    </div>
                                </div>
                            </div>
                            @if ($stack->has_image)
                                <div class="form-check">
                                    {!! Form::checkbox('remove_credit', 1, false, ['class' => 'form-check-input']) !!}
                                    {!! Form::label('remove_credit', 'Remove current credits', ['class' => 'form-check-label']) !!}
                                </div>
                            @endif
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                        </div>
                        {!! Form::close() !!}
                    </li>
                @endif
                @if ($stack->isTransferrable || $user->hasPower('edit_inventories'))
                    @if (!$stack->character_id)
                        <li class="list-group-item">
                            <a class="card-title h5 collapse-title" data-toggle="collapse" href="#transferForm">
                                @if ($stack->user_id != $user->id)
                                    [ADMIN]
                                @endif Transfer Pet
                            </a>
                            {!! Form::open(['url' => 'pets/transfer/' . $stack->id, 'id' => 'transferForm', 'class' => 'collapse']) !!}
                            @if (!$stack->isTransferrable)
                                <p class="alert alert-warning my-2">This pet is account-bound, but your rank allows you to transfer it to another user.</p>
                            @endif
                            <div class="form-group">
                                {!! Form::label('user_id', 'Recipient') !!} {!! add_help('You can only transfer pets to verified users.') !!}
                                {!! Form::select('user_id', $userOptions, null, ['class' => 'form-control']) !!}
                            </div>
                            <div class="text-right">
                                {!! Form::submit('Transfer', ['class' => 'btn btn-primary']) !!}
                            </div>
                            {!! Form::close() !!}
                        </li>
                    @else
                        <li class="list-group-item bg-light">
                            <h5 class="card-title mb-0 text-muted"><i class="fas fa-lock mr-2"></i> Currently attached to {!! $stack->character->displayName !!}</h5>
                        </li>
                    @endif
                @else
                    <li class="list-group-item bg-light">
                        <h5 class="card-title mb-0 text-muted"><i class="fas fa-lock mr-2"></i> Account-bound</h5>
                    </li>
                @endif
                <li class="list-group-item">
                    <a class="card-title h5 collapse-title" data-toggle="collapse" href="#deleteForm">
                        @if ($stack->user_id != $user->id)
                            [ADMIN]
                        @endif Delete Pet
                    </a>
                    {!! Form::open(['url' => 'pets/delete/' . $stack->id, 'id' => 'deleteForm', 'class' => 'collapse']) !!}
                    <p>This action is not reversible. Are you sure you want to delete this pet?</p>
                    <div class="text-right">
                        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                    </div>
                    {!! Form::close() !!}
                </li>
            </ul>
        </div>
    @endif
@endif
