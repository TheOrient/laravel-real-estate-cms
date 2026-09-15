@foreach ($categories as $category)
    <option value="{{ $category->id }}" {{ in_array($category->id, $selected) ? 'selected' : '' }}>
        {!! str_repeat('&nbsp;&nbsp;', $level) !!} {{ $category->name }}
    </option>
    @if ($category->childrenRecursive && $category->childrenRecursive->count() > 0)
        <x-category-three :categories="$category->children" :selected="$selected" :level="$level + 1" />
    @endif
@endforeach
