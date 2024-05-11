<div class="row world-entry">
    @if ($variant->imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $variant->imageUrl }}" data-lightbox="entry" data-title="{{ $variant->name }}">
            <img src="{{ $variant->imageUrl }}" class="world-entry-image" alt="{{ $variant->name }}" /></a>
        </div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Item" :object="$variant" />
        <h5>
            @if (!$variant->is_released)
                <i class="fas fa-eye-slash mr-1"></i>
            @endif
            {!! $variant->displayName !!}
            @if (isset($variant->idUrl) && $variant->idUrl)
                <a href="{{ $variant->idUrl }}" class="world-entry-search text-muted">
                    <i class="fas fa-search"></i>
                </a>
            @endif
        </h5>
        <div class="row">
            @if (isset($variant->category) && $variant->category)
                <div class="col-md">
                    <p>
                        <strong>Category:</strong>
                        @if (!$variant->category->is_visible)
                            <i class="fas fa-eye-slash mx-1 text-danger"></i>
                        @endif
                        <a href="{!! $variant->category->url !!}">
                            {!! $variant->category->name !!}
                        </a>
                    </p>
                </div>
            @endif
            @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
                @if (isset($variant->rarity) && $variant->rarity)
                    <div class="col-md">
                        <p><strong>Rarity:</strong> {!! $variant->rarity->displayName !!}</p>
                    </div>
                @endif
                @if (isset($variant->itemArtist) && $variant->itemArtist)
                    <div class="col-md">
                        <p><strong>Artist:</strong> {!! $variant->itemArtist !!}</p>
                    </div>
                @endif
            @endif
            @if (isset($variant->data['resell']) && $variant->data['resell'] && App\Models\Currency\Currency::where('id', $variant->resell->flip()->pop())->first() && config('lorekeeper.extensions.item_entry_expansion.resale_function'))
                <div class="col-md">
                    <p><strong>Resale Value:</strong> {!! App\Models\Currency\Currency::find($variant->resell->flip()->pop())->display($variant->resell->pop()) !!}</p>
                </div>
            @endif
            <div class="col-md-6 col-md">
                <div class="row">
                    @foreach ($variant->tags as $tag)
                        @if ($tag->is_active)
                            <div class="col">
                                {!! $tag->displayTag !!}
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="world-entry-text">
            @if (isset($variant->reference) && $variant->reference && config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
                <p>
                    <strong>Reference Link:</strong>
                    <a href="{{ $variant->reference }}">
                        {{ $variant->reference }}
                    </a>
                </p>
            @endif
            {!! $description !!}
            @if (((isset($variant->uses) && $variant->uses) || (isset($variant->source) && $variant->source) || $shops->count() || (isset($variant->data['prompts']) && $variant->data['prompts'])) && config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
                <div class="text-right">
                    <a data-toggle="collapse" href="#item-{{ $variant->id }}" class="text-primary">
                        <strong>Show details...</strong>
                    </a>
                </div>
                <div class="collapse" id="item-{{ $variant->id }}">
                    @if (isset($variant->uses) && $variant->uses)
                        <p>
                            <strong>Uses:</strong> {{ $variant->uses }}
                        </p>
                    @endif
                    @if ((isset($variant->source) && $variant->source) || $shops->count() || (isset($variant->data['prompts']) && $variant->data['prompts']))
                        <h5>Availability</h5>
                        <div class="row">
                            @if (isset($variant->source) && $variant->source)
                                <div class="col">
                                    <p>
                                        <strong>Source:</strong>
                                    </p>
                                    <p>
                                        {!! $variant->source !!}
                                    </p>
                                </div>
                            @endif
                            @if ($shops->count())
                                <div class="col">
                                    <p>
                                        <strong>Purchaseable At:</strong>
                                    </p>
                                    <div class="row">
                                        @foreach ($shops as $shop)
                                            <div class="col">
                                                <a href="{{ $shop->url }}">
                                                    {{ $shop->name }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if (isset($variant->data['prompts']) && $variant->data['prompts'])
                                <div class="col">
                                    <p>
                                        <strong>Drops From:</strong>
                                    </p>
                                    <div class="row">
                                        @foreach ($variant->prompts as $prompt)
                                            <div class="col">
                                                <a href="{{ $prompt->url }}">
                                                    {{ $prompt->name }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
            @if ($variant->children->count())
                <h3>Item Variants</h3>
                <div class="row">
                    @foreach($variant->children as $i)
                        <div class="col-md-4">
                            @include('world._item_entry', ['item' => $i])
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
