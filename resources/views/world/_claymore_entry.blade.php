{{-- for use with gear or weapons --}}
<div class="row world-entry">
    @if ($imageUrl)
        <div class="col-md-3 world-entry-image">
            <a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}">
                <img src="{{ $imageUrl }}" class="rounded world-entry-image" />
            </a>
        </div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        @if (isset($edit))
            <x-admin-edit title="{{ $edit['title'] }}" :object="$edit['object']" />
        @endif
        <h3>
            @if (!$visible)
                <i class="fa fa-eye-slash mr-1"></i>
            @endif
            {!! $name !!}
            @if ($item?->category)
                ({!! $item->category->displayName !!})
            @endif
            @if (isset($idUrl) && $idUrl)
                <a href="{{ $idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>
            @endif
        </h3>
        <div class="world-entry-text">
            <h5>@include('world._typing', ['object' => $item])</h5>
            <p>{!! $description !!}</p>
            @if ($item->parent || $item->children->count() || $item->stats->count())
                <hr />
            @endif
            @if ($item->parent)
                <h5 class="alert alert-secondary">Upgrade of: {!! $item->parent->displayName !!}</h5>
            @endif
            @if ($item->children->count())
                <h5 class="alert alert-info">Upgrades:</h5>
                @foreach ($item->children as $child)
                    <div class="card">
                        <h5 class="card-header inventory-header">
                            <a class="inventory-collapse-toggle collapse-toggle collapsed" href="#drop-collapse" data-toggle="collapse">
                                @if ($child->has_image)
                                    <img src="{{ $child->imageUrl }}" class="rounded world-entry-image" style="max-height: 50px;" />
                                @endif
                                {!! $child->name !!}
                            </a>
                        </h5>
                        <div class="collapse" id="drop-collapse">
                            <div class="card-body">
                                @if (count(getLimits($child)))
                                    @include('widgets._limits', [
                                        'object' => $child,
                                        'hideUnlock' => true,
                                    ])
                                @else
                                    No upgrade cost set.
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
            @if ($item->stats?->count())
                <h4>Stats</h4>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th width="70%">Stat</th>
                            <th width="30%">Bonus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item->stats as $stat)
                            <tr>
                                <td>{!! $stat->stat->name !!}</td>
                                <td>{{ $stat->count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
