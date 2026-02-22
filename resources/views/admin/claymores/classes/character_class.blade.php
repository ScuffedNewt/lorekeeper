@extends('admin.layout')

@section('admin-title')
    Character Class
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Character Class' => 'admin/character-classes']) !!}

    <h1>Character Class</h1>

    @if (!config('lorekeeper.claymores_and_companions.visibility_settings.character_classes'))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Character classes are currently set to be hidden. To change this, update the visibility settings in the config.
        </div>
    @endif

    <p>This is a list of character class that will be used to classify characters. Creating character class is entirely optional, but recommended for organisational purposes.</p>
    <p>The sorting order reflects the order in which the character class will be displayed on the world pages.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/character-classes/create') }}"><i class="fas fa-plus"></i> Create New Character Class</a></div>
    @if (!count($class))
        <p>No character classes found.</p>
    @else
        <table class="table table-sm category-table">
            <thead>
                <tr>
                    <th>Class</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($class as $category)
                    <tr>
                        <td>
                            @if (!$category->is_visible)
                                <i class="fas fa-eye-slash mr-1"></i>
                            @endif
                            {!! $category->displayName !!}
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/character-classes/edit/' . $category->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#sortable").sortable({
                characters: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
