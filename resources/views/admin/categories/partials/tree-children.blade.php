{{-- resources/views/admin/categories/partials/tree-children.blade.php --}}
@if($children->count() > 0)
<div class="tree-children" style="margin-left: 30px; padding-left: 15px; border-left: 2px dashed #5C6844;">
    @foreach($children as $child)
        @php
            $grandChildren = $categories->where('parent_id', $child->id_category);
            $postCount = DB::table('posts')->where('id_post_category', $child->id_category)->count();
        @endphp
        <div class="tree-child mb-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-folder me-2" style="color: #5C6844;"></i>
                    <strong>{{ $child->category_name }}</strong>
                    @if($grandChildren->count() > 0)
                        <span class="badge bg-info ms-2">{{ $grandChildren->count() }} sub</span>
                    @endif
                    <span class="badge bg-secondary ms-1">{{ $postCount }} post</span>
                </div>
                <div>
                    <a href="{{ route('admin.categories.edit', $child->id_category) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
            </div>
            @if($grandChildren->count() > 0)
                @include('admin.categories.partials.tree-children', ['children' => $grandChildren, 'level' => $level + 1])
            @endif
        </div>
    @endforeach
</div>
@endif