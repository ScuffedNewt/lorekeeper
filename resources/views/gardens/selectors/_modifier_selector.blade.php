@php
    $selectors = getGardenObjectTypes('modifier');
    $modifierUserObjects = [];
    foreach ($user_garden_objects as $key => $objects) {
        foreach ($objects as $object_type => $stacks) {
            if (array_key_exists($object_type, $selectors)) {
                $modifierUserObjects[$key][$object_type] = $stacks;
            }
        }
    }
@endphp
<div class="text-center card">
    <div class="garden-scroll" data-toggle="buttons">

        {{-- deselect --}}
        <div class="garden-tile">
            <div class="btn mx-1 btn-block garden-button" data-garden-id="deselect">
                <i class="fas fa-times fa-5x text-white"></i>
                <div class="text-small text-white text-center" style="font-size:1rem;">Deselect</div>
            </div>
        </div>

        {{-- modifier objects --}}
        @foreach ($modifierUserObjects as $type => $objects)
            {{-- Add more types here --}}
            @foreach ($objects as $object_type => $stacks)
                @if (count($stacks))
                    @foreach ($stacks as $stack)
                        @if (get_class($stack) == 'App\Models\User\UserItem')
                            @include('gardens.selectors.types._user_item', [
                                'object_type' => $object_type,
                                'stack_type' => $type,
                                'stack' => $stack,
                                'garden' => $garden,
                                'flat' => true,
                            ])
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endforeach
    </div>
</div>
