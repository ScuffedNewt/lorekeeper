<div class="text-center card" style="overflow-y: scroll;">
    <div class=" p-2 btn-group-vertical btn-group-toggle text-center justify-content-center" data-toggle="buttons">
        <div class="btn my-1 btn-block garden-button" data-garden-id="deselect">
            <i class="fas fa-times fa-5x text-white"></i>
            <div class="text-small text-white text-center" style="font-size:1rem;">Deselect</div>
        </div>
        @foreach ($user_garden_objects as $type => $objects)
            {{-- Add more types here --}}
            @foreach ($objects as $object_type => $stacks)
                @if (count($stacks))
                    @foreach ($stacks as $stack)
                        @if (get_class($stack) == 'App\Models\User\UserItem')
                            @include('gardens.selectors._user_item', [
                                'object_type' => $object_type,
                                'stack_type' => $type,
                                'stack' => $stack,
                                'garden' => $garden,
                            ])
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endforeach
    </div>
</div>
