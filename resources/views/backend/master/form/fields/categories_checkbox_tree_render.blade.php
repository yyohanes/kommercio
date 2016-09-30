<li>
    <label>
        <input {{ in_array($item->id, old($key, $existing))?'checked':'' }} type="checkbox" name="{{ $name }}" value="{{ $item->id }}"> <span class="checkbox-label">{{ $item->name }}</span></label>

        @if($item->children->count() > 0)
        <ul class="list-unstyled">
            @foreach($item->children as $child)
                @include('backend.master.form.fields.categories_checkbox_tree_render', [
                    'item' => $child
                ])
            @endforeach
        </ul>
        @endif
</li>