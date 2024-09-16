<div class="row world-entry">
    @if ($imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" alt="{{ $name }}" /></a></div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Element" :object="$element" />
        <h3>
            {!! $name !!}
            @if (isset($idUrl) && $idUrl)
                <a href="{{ $idUrl }}" class="world-entry-search text-muted">
                    <i class="fas fa-search"></i>
                </a>
            @endif
            @if (isset($searchUrl) && $searchUrl)
                <a href="{{ $searchUrl }}" class="world-entry-search text-muted">
                    <i class="fas fa-list"></i>
                </a>
            @endif
        </h3>
        <div class="world-entry-text">
            {!! $description !!}
            <div class="text-right">
                <a data-toggle="collapse" href="#element-{{ $element->id }}" class="text-primary">
                    <strong>Show details...</strong>
                </a>
            </div>
            <div class="collapse" id="element-{{ $element->id }}">
                <h5>Strengths</h5>
                @if ($element->strengths->count())
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th width="70%">Name</th>
                                <th width="30%">Multiplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($element->strengths as $strength)
                                <tr>
                                    <td>{!! $strength->element->displayName !!}</td>
                                    <td>x{{ $strength->multiplier }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>No Strengths Found</p>
                @endif
                <h5>Weaknesses</h5>
                @if ($element->weaknesses->count())
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th width="70%">Name</th>
                                <th width="30%">Multiplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($element->weaknesses as $weakness)
                                <tr>
                                    <td>{!! $weakness->weakness->displayName !!}</td>
                                    <td>x{{ $weakness->multiplier }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>No Weaknesses Found</p>
                @endif
                <h5>Immunities</h5>
                @if ($element->immunities->count())
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($element->immunities as $immunity)
                                <tr>
                                    <td>{!! $immunity->immunity->displayName !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>No Immunities Found</p>
                @endif
            </div>
        </div>
    </div>
</div>
