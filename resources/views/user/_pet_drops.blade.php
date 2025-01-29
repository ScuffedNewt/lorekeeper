@if (!$pet->drops->dropData->isActive)
    <div class="alert alert-warning">This pet's drops are currently inactive. Because you are staff, you can see this area anyways.</div>
@endif

<h4>
    Collect {{ isset($pet->drops->dropData->name) ? $pet->drops->dropData->name . 's' : 'Drops' }} ({{ $pet->drops->parameters }})
    {!! add_help('Your pet\'s type is ' . $pet->drops->parameters . '.<br>You can view all pet drops on the ' . $pet->pet->name . ' pet page.') !!}
    @if (Auth::check() && Auth::user()->hasPower('edit_inventories'))
        <a href="#" class="float-right btn btn-outline-info btn-sm" id="paramsButton" data-toggle="modal" data-target="#paramsModal"><i class="fas fa-cog"></i> Admin</a>
    @endif
</h4>
<div class="alert alert-info mt-3">
    <i class="fas fa-info-circle"></i> Drops every {{ $pet->drops->dropData->interval }}.
</div>
<a class="btn btn-primary mb-2" data-toggle="collapse" href="#drops" role="button" aria-expanded="false" aria-controls="drops">
    View Drops
</a>

<div class="card card-body mb-4 collapse" id="drops">
    @if ($pet->availableDrops)
        <p>This pet produces these {{ isset($pet->drops->dropData->name) ? strtolower($pet->drops->dropData->name) . 's' : 'drops' }}, based on their type of pet and/or variant:</p>
        <table class="table table-sm category-table">
            <thead>
                <tr>
                    <th width="50%">Reward</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($pet->availableDrops->rewards(true)[strtolower($pet->drops->parameters)]))
                    @foreach ($pet->availabledrops->rewards(true)[strtolower($pet->drops->parameters)] as $reward)
                        <tr>
                            @php $reward_object = $reward->rewardable_type::find($reward->rewardable_id); @endphp
                            <td>
                                @if ($reward_object->has_image)
                                    <img class="img-fluid" style="max-height: 10em;" src="{{ $reward_object->imageUrl }}"><br />
                                @endif
                                {!! $reward_object->displayName !!}
                            </td>
                            <td>Between {{ $reward->min_quantity . ' and ' . $reward->max_quantity }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>No drops available for this pet.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @else
        <p>This pet {{ isset($pet->drops->dropData->name) ? 'doesn\'t produce any ' . strtolower($pet->drops->dropData->name) . 's' : 'isn\'t eligible for any drops' }}.</p>
    @endif

    @if ($pet->availableDrops)
        <div class="text-center">
            <p>
                This pet has {{ $drops->drops_available }} batch{{ $drops->drops_available == 1 ? '' : 'es' }} of {{ isset($pet->drops->dropData->name) ? strtolower($pet->drops->dropData->name) : 'drop' }}s available.<br />
                @if (isset($drops->dropData->cap) && $drops->dropData->cap > 0)
                    This pet can manage a maximum of {{ $drops->dropData->cap }} batch{{ $drops->dropData->cap == 1 ? '' : 'es' }} of {{ isset($pet->drops->dropData->name) ? strtolower($pet->drops->dropData->name) : 'drop' }}s at once!
                    @if ($drops->drops_available >= $drops->dropData->cap)
                        Until these {{ isset($pet->drops->dropData->name) ? strtolower($pet->drops->dropData->name) : 'drop' }}s are collected, this pet won't produce any more.
                    @else
                        This pet's next {{ isset($pet->drops->dropData->name) ? strtolower($pet->drops->dropData->name) : 'drop' }}(s) will be available to collect {!! pretty_date($drops->next_day) !!}.
                    @endif
                @else
                    This pet's next {{ isset($pet->drops->dropData->name) ? strtolower($pet->drops->dropData->name) : 'drop' }}(s) will be available to collect {!! pretty_date($drops->next_day) !!}.
                @endif
            </p>
        </div>
        @if (Auth::check() && Auth::user()->id == $pet->user_id && $drops->drops_available > 0)
            {!! Form::open(['url' => 'pets/collect/' . $pet->id]) !!}
            {!! Form::submit('Collect ' . (isset($pet->drops->dropData->name) ? $pet->drops->dropData->name : 'Drop') . ($drops->drops_available > 1 ? 's' : ''), ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        @endif
    @endif
</div>

@if (Auth::check() && Auth::user()->hasPower('edit_inventories'))
    <div class="modal fade" id="paramsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">[ADMIN] Adjust Drop</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['url' => 'admin/pets/pet/' . $pet->id]) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('parameters', 'Group') !!}
                                {!! Form::select('parameters', $drops->dropData->parameterArray, $drops->parameters, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('drops_available', 'Drops Available') !!}
                                {!! Form::number('drops_available', $drops->drops_available, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endif
