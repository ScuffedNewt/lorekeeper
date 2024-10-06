@php
    $limitTypes = collect(config('lorekeeper.limits.limit_types'))->map(function ($value, $key) {
        return $value['name'];
    });
    $limits = \App\Models\Limit\Limit::hasLimits($object) ? \App\Models\Limit\Limit::getLimits($object) : null;
@endphp

@if ($limits)
    @if (!isset($compact) || !$compact)
        <h4 class="my-3">{!! $object->displayName !!}'s Requirements</h4>

        <p>
            You must obtain or complete all of the following in order to access this {{ $object->assetType ? (substr($object->assetType, -1) === 's' ? substr($object->assetType, 0, -1) : $object->assetType) : '' }}.
        </p>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th width="30%">Limit Type</th>
                    <th width="30%">Limit</th>
                    <th width="20%">Quantity</th>
                    <th width="20%">Is Debited?</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($limits as $limit)
                    <tr>
                        <td data-toggle="tooltip" title="{{ config('lorekeeper.limits.limit_types')[$limit->limit_type]['description'] }}">
                            <i class="fas fa-question-circle"></i>
                            {{ $limitTypes[$limit->limit_type] }}
                        </td>
                        <td>{!! $limit->limit->displayName !!}</td>
                        <td>{{ $limit->quantity }}</td>
                        <td class="text-{{ $limit->debit ? 'success' : 'danger' }}">
                            {{ $limit->debit ? 'Yes' : 'No' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if (!$limits->first()->isUnlocked(Auth::user() ?? null) && !$limits->first()->is_auto_unlocked)
            <div class="alert alert-secondary p-0 mt-2 mb-0">
                {!! Form::open(['url' => 'limits/unlock/' . $limits->first()->id]) !!}
                {!! Form::submit('Unlock', ['class' => 'btn btn-sm btn-secondary']) !!}
                {!! Form::close() !!}
            </div>
        @endif
    @else
        <div class="alert alert-{{ $limits->first()->isUnlocked(Auth::user() ?? null) ? 'info' : 'danger' }} p-0 mt-2">
            <small>
                (Requires {!! implode(
                    ', ',
                    $limits->map(function ($limit) use ($limitTypes) {
                            return ($limit->quantity ? $limit->quantity . ' ' : '') . $limit->limit->displayName;
                        })->toArray(),
                ) !!})
                @if (!$limits->first()->isUnlocked(Auth::user() ?? null) && !$limits->first()->is_auto_unlocked)
                    <div class="alert alert-secondary p-0 mt-2 mb-0">
                        <small>
                            {!! Form::open(['url' => 'limits/unlock/' . $limits->first()->id]) !!}
                            {!! Form::submit('Unlock', ['class' => 'btn btn-sm btn-secondary']) !!}
                            {!! Form::close() !!}
                        </small>
                    </div>
                @endif
            </small>
        </div>
    @endif
@endif
