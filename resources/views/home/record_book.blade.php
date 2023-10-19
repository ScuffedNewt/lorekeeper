@extends('home.layout')

@section('home-title')
    Record Book
@endsection

@section('home-content')
    {!! breadcrumbs(['Record Book' => 'record-book']) !!}
    <h1>
        My Record Book
    </h1>

    <p>Here is a record of all items you've ever owned, and ones you've yet to receive.</p>

    <style>
        img {
            transition: all 0.2s ease-in-out;
        }

        img:hover {
            scale: 1.1;
        }
    </style>

    @foreach ($items as $categoryId => $categoryItems)
        <div class="card mb-2">
            <h5 class="card-header">
                {!! isset($categories[$categoryId]) ? '<a href="' . $categories[$categoryId]->searchUrl . '">' . $categories[$categoryId]->name . '</a>' : 'Miscellaneous' !!}
                <a class="small inventory-collapse-toggle collapse-toggle" href="#categoryId_{!! isset($categories[$categoryId]) ? $categories[$categoryId]->id : 'miscellaneous' !!}" data-toggle="collapse">Show</a>
            </h5>
            <div class="card-body p-2 collapse show row" id="categoryId_{!! isset($categories[$categoryId]) ? $categories[$categoryId]->id : 'miscellaneous' !!}">
                @foreach ($categoryItems as $item)
                    <div class="col-lg-3 col-sm-4 col-12 text-center">
                        <a href="{{ $item->idUrl }}">
                            @php
                                $check = !$user
                                    ->items()
                                    ->where('item_id', $item->id)
                                    ->exists();
                            @endphp
                            @if ($item->has_image)
                                <img src="{{ $item->imageUrl }}" class="img-fluid" style="{{ $check ? 'filter: grayscale(100%)  blur(.15em); opacity: 0.75;' : '' }}" alt="{{ $item->name }}" />
                            @endif
                            <div>
                                {!! $check ? '<p class="text-danger mb-0">Not Unlocked</p>' : '' !!}
                                <h5>{{ $item->name }}</h5>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endsection
