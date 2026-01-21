@php
    $renderTree = function ($parentId, $level = 0) use (&$renderTree, $grouped) {
        $items = $grouped->get($parentId, collect());
        if ($items->isEmpty()) {
            return;
        }

        echo '<div class="'.($level > 0 ? 'tree-indent mt-2' : 'tree-list list-group').'">';

        foreach ($items as $item) {
            $children = $grouped->get($item->id, collect());
            $hasChildren = $children->isNotEmpty();
            $collapseId = 'node-children-'.$item->id;
            $expandedClass = $item->is_expanded ? ' show' : '';
            $expandedAria = $item->is_expanded ? 'true' : 'false';

            echo '<div class="list-group-item">';
            echo '<div class="tree-row">';

            if ($hasChildren) {
                echo '<button class="btn btn-light tree-toggle" data-toggle="collapse" data-target="#'.$collapseId.'" aria-expanded="'.$expandedAria.'" data-node-id="'.e($item->id).'">';
                echo '<i class="fas fa-chevron-right"></i>';
                echo '</button>';
            } else {
                echo '<span class="tree-toggle text-muted"><i class="far fa-file"></i></span>';
            }

            echo '<div>';
            echo '<div class="tree-title">'.e($item->title).'</div>';
            if ($item->description) {
                echo '<div class="tree-meta">'.e($item->description).'</div>';
            }
            echo '</div>';

            echo '<div class="tree-actions btn-group btn-group-sm">';
            echo '<button class="btn btn-outline-primary js-add-child" data-parent-id="'.e($item->id).'" data-parent-title="'.e($item->title).'" data-toggle="modal" data-target="#nodeModal">';
            echo 'Добавить';
            echo '</button>';
            $imageUrl = $item->image_path
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path)
                : asset('default_avatar.png');
            echo '<button class="btn btn-outline-secondary js-edit-node" data-node-id="'.e($item->id).'" data-node-title="'.e($item->title).'" data-node-description="'.e($item->description ?? '').'" data-node-sort="'.e($item->sort_order).'" data-node-expanded="'.e($item->is_expanded ? '1' : '0').'" data-node-parent-id="'.e($item->parent_id ?? '').'" data-node-parent-title="'.e(optional($item->parent)->title ?? 'Корень').'" data-node-image-url="'.e($imageUrl).'" data-toggle="modal" data-target="#nodeEditModal">';
            echo 'Редактировать';
            echo '</button>';
            if (!$hasChildren) {
                echo '<button class="btn btn-outline-danger js-delete-node" data-node-id="'.e($item->id).'" data-node-title="'.e($item->title).'" data-toggle="modal" data-target="#nodeDeleteModal">';
                echo 'Удалить';
                echo '</button>';
            }
            echo '</div>';

            echo '</div>';

            if ($hasChildren) {
                echo '<div id="'.$collapseId.'" class="collapse'.$expandedClass.' mt-2" data-node-id="'.e($item->id).'">';
                $renderTree($item->id, $level + 1);
                echo '</div>';
            }

            echo '</div>';
        }

        echo '</div>';
    };
@endphp

@if ($grouped->isEmpty())
    <span class="d-none" data-has-root="0"></span>
    <div class="alert alert-light border mb-0">
        Пока нет элементов. Нажмите "Добавить корневой элемент", чтобы начать.
    </div>
@else
    <span class="d-none" data-has-root="{{ $grouped->has(0) ? '1' : '0' }}"></span>
    @php $renderTree(0); @endphp
@endif
