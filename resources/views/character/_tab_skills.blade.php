@foreach ($skills->chunk(2) as $chunk)
    <div class="row">
        @foreach ($chunk as $skill)
            <div class="col-md">
                <div class="text-center">
                    <h5>{{ $skill->name }}</h5>

                    @if ($character->skills()->where('skill_id', $skill->id)->exists())
                        @php
                            $characterSkill = $character->skills()->where('skill_id', $skill->id)->first();
                        @endphp
                        <p>Level: {{ $characterSkill->level }}</p>
                    @else
                        <p class="mx-auto text-center">Not unlocked.</p>
                        @if ($skill->prerequisite)
                            <p class="text-center">Requires {!! $skill->prerequisite->displayname !!}</p>
                        @endif
                    @endif
                </div> {{-- Close .text-center --}}

                @if ($character->skills()->where('skill_id', $skill->id)->exists() && $skill->children->count())
                    <div class="row">
                        @foreach ($skill->children as $children)
                            <div class="col-md mx-auto body children-body children-scroll">
                                <div class="children-skill">
                                    <ul>
                                        @include('character._skill_children', ['children' => $children, 'skill' => $skill])
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div> {{-- Close .col-md --}}
        @endforeach
    </div> {{-- Close .row --}}
@endforeach

@if ($skills->isEmpty())
    <p class="text-center">No available skills.</p>
@endif

<script>
    $(function() {
        $('.children-skill ul').hide();
        $('.children-skill > ul').show();
        $('.children-skill ul.active').show();

        $('.children-skill li').on('click', function(e) {
            var children = $(this).find('> ul');
            if (children.is(":visible")) {
                children.hide('fast').removeClass('active');
            } else {
                children.show('fast').addClass('active');
            }
            e.stopPropagation();
        });
    });
</script>
