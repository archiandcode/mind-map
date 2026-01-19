@component('layouts.adminlte', ['title' => 'Узлы'])
    <style>
        .tree-list { border: 1px solid #dee2e6; border-radius: 0.25rem; overflow: hidden; }
        .tree-list .list-group-item { border: 0; border-bottom: 1px solid #dee2e6; }
        .tree-list .list-group-item:last-child { border-bottom: 0; }
        .tree-row { display: flex; align-items: center; gap: 10px; }
        .tree-title { font-weight: 600; }
        .tree-meta { font-size: 12px; color: #6c757d; }
        .tree-indent { margin-left: 24px; border-left: 1px dashed #dee2e6; }
        .tree-toggle { width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; }
        .tree-toggle.btn { padding: 0; }
        .tree-actions { margin-left: auto; }
    </style>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="card-title mb-0">Иерархия</h3>
                <button class="btn btn-primary btn-sm js-add-child" data-parent-id="" data-parent-title="Корень" data-toggle="modal" data-target="#nodeModal">
                    Добавить корневой узел
                </button>
            </div>

            <div id="nodes-tree">
                @include('nodes._tree', ['grouped' => $grouped])
            </div>
        </div>
    </div>

    <div class="modal fade" id="nodeModal" tabindex="-1" role="dialog" aria-labelledby="nodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content" action="{{ route('nodes.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="nodeModalLabel">Создать узел</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modal-errors" class="alert alert-danger d-none"></div>
                    <input type="hidden" name="parent_id" id="modal-parent-id">
                    <div class="form-group">
                        <label for="modal-parent-title">Родитель</label>
                        <input type="text" id="modal-parent-title" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="modal-title">Название</label>
                        <input type="text" id="modal-title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="modal-description">Описание</label>
                        <textarea id="modal-description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="modal-sort-order">Порядок сортировки</label>
                            <input type="number" id="modal-sort-order" name="sort_order" class="form-control" value="0" min="0" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="modal-image">Изображение</label>
                            <input type="file" id="modal-image" name="image" class="form-control-file">
                            <img id="modal-image-preview" class="img-thumbnail mt-2 d-none" alt="Предпросмотр">
                        </div>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" id="modal-expanded" name="is_expanded" class="form-check-input" value="1">
                        <label class="form-check-label" for="modal-expanded">Развернуть по умолчанию</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="nodeEditModal" tabindex="-1" role="dialog" aria-labelledby="nodeEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content" id="nodeEditForm" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-header">
                    <h5 class="modal-title" id="nodeEditModalLabel">Редактировать узел</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="edit-modal-errors" class="alert alert-danger d-none"></div>
                    <input type="hidden" name="parent_id" id="edit-parent-id">
                    <div class="form-group">
                        <label for="edit-parent-title">Родитель</label>
                        <input type="text" id="edit-parent-title" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit-title">Название</label>
                        <input type="text" id="edit-title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-description">Описание</label>
                        <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit-sort-order">Порядок сортировки</label>
                            <input type="number" id="edit-sort-order" name="sort_order" class="form-control" value="0" min="0" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit-image">Изображение</label>
                            <input type="file" id="edit-image" name="image" class="form-control-file">
                            <img id="edit-image-preview" class="img-thumbnail mt-2 d-none" alt="Текущее изображение">
                        </div>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" id="edit-expanded" name="is_expanded" class="form-check-input" value="1">
                        <label class="form-check-label" for="edit-expanded">Развернуть по умолчанию</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="nodeDeleteModal" tabindex="-1" role="dialog" aria-labelledby="nodeDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content" id="nodeDeleteForm" method="post">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <div class="modal-header">
                    <h5 class="modal-title" id="nodeDeleteModalLabel">Удалить узел</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="delete-modal-errors" class="alert alert-danger d-none"></div>
                    <p class="mb-0">Удалить <strong id="delete-node-title"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const storagePrefix = 'nodes.collapse.';

        function saveExpandedState() {
            const collapseNodes = document.querySelectorAll('.collapse[data-node-id]');
            collapseNodes.forEach((el) => {
                const nodeId = el.getAttribute('data-node-id');
                if (el.classList.contains('show')) {
                    localStorage.setItem(storagePrefix + nodeId, 'open');
                } else {
                    localStorage.setItem(storagePrefix + nodeId, 'closed');
                }
            });
        }

        async function reloadTree(treeContainer) {
            const treeResponse = await fetch('{{ route('nodes.tree') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (treeResponse.ok) {
                const html = await treeResponse.text();
                treeContainer.innerHTML = html;
                initTree(true);
            }
        }

        function initTree(restoreState = false) {
            const collapseNodes = document.querySelectorAll('.collapse[data-node-id]');
            const toggleButtons = document.querySelectorAll('.tree-toggle[data-node-id]');

            if (restoreState) {
                collapseNodes.forEach((el) => {
                    const nodeId = el.getAttribute('data-node-id');
                    const saved = localStorage.getItem(storagePrefix + nodeId);
                    if (saved === 'open') {
                        el.classList.add('show');
                    }
                });
            }

            toggleButtons.forEach((btn) => {
                const target = btn.getAttribute('data-target');
                const panel = target ? document.querySelector(target) : null;
                if (!panel) return;
                btn.setAttribute('aria-expanded', panel.classList.contains('show') ? 'true' : 'false');
            });

            collapseNodes.forEach((el) => {
                const nodeId = el.getAttribute('data-node-id');
                el.addEventListener('shown.bs.collapse', () => {
                    localStorage.setItem(storagePrefix + nodeId, 'open');
                    const toggle = document.querySelector('.tree-toggle[data-node-id="' + nodeId + '"]');
                    if (toggle) toggle.setAttribute('aria-expanded', 'true');
                });
                el.addEventListener('hidden.bs.collapse', () => {
                    localStorage.setItem(storagePrefix + nodeId, 'closed');
                    const toggle = document.querySelector('.tree-toggle[data-node-id="' + nodeId + '"]');
                    if (toggle) toggle.setAttribute('aria-expanded', 'false');
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const treeContainer = document.getElementById('nodes-tree');
            const parentIdField = document.getElementById('modal-parent-id');
            const parentTitleField = document.getElementById('modal-parent-title');
            const editParentIdField = document.getElementById('edit-parent-id');
            const editParentTitleField = document.getElementById('edit-parent-title');
            const rootAddButton = document.querySelector('.js-add-child[data-parent-title="Корень"]');
            const createImageInput = document.getElementById('modal-image');
            const createImagePreview = document.getElementById('modal-image-preview');
            const editImageInput = document.getElementById('edit-image');
            const editImagePreview = document.getElementById('edit-image-preview');

            initTree(false);

            if (rootAddButton) {
                rootAddButton.addEventListener('click', () => {
                    parentIdField.value = '';
                    parentTitleField.value = 'Корень';
                    createImagePreview.classList.add('d-none');
                    createImagePreview.removeAttribute('src');
                });
            }

            createImageInput.addEventListener('change', () => {
                const file = createImageInput.files[0];
                if (!file) {
                    createImagePreview.classList.add('d-none');
                    createImagePreview.removeAttribute('src');
                    return;
                }
                const url = URL.createObjectURL(file);
                createImagePreview.src = url;
                createImagePreview.classList.remove('d-none');
            });

            editImageInput.addEventListener('change', () => {
                const file = editImageInput.files[0];
                if (!file) {
                    return;
                }
                const url = URL.createObjectURL(file);
                editImagePreview.src = url;
                editImagePreview.classList.remove('d-none');
            });

            treeContainer.addEventListener('click', (event) => {
                const addBtn = event.target.closest('.js-add-child');
                if (addBtn) {
                    const parentId = addBtn.getAttribute('data-parent-id') || '';
                    const parentTitle = addBtn.getAttribute('data-parent-title') || 'Корень';
                    parentIdField.value = parentId;
                    parentTitleField.value = parentTitle;
                    createImagePreview.classList.add('d-none');
                    createImagePreview.removeAttribute('src');
                    return;
                }

                const editBtn = event.target.closest('.js-edit-node');
                if (editBtn) {
                    const nodeId = editBtn.getAttribute('data-node-id');
                    const title = editBtn.getAttribute('data-node-title') || '';
                    const description = editBtn.getAttribute('data-node-description') || '';
                    const sortOrder = editBtn.getAttribute('data-node-sort') || '0';
                    const expanded = editBtn.getAttribute('data-node-expanded') === '1';
                    const parentId = editBtn.getAttribute('data-node-parent-id') || '';
                    const parentTitle = editBtn.getAttribute('data-node-parent-title') || 'Корень';
                    const imageUrl = editBtn.getAttribute('data-node-image-url') || '';

                    document.getElementById('nodeEditForm').action = '{{ url('/nodes') }}/' + nodeId;
                    document.getElementById('edit-title').value = title;
                    document.getElementById('edit-description').value = description;
                    document.getElementById('edit-sort-order').value = sortOrder;
                    document.getElementById('edit-expanded').checked = expanded;
                    editParentIdField.value = parentId;
                    editParentTitleField.value = parentTitle;
                    document.getElementById('edit-image').value = '';
                    document.getElementById('edit-modal-errors').classList.add('d-none');
                    if (imageUrl) {
                        editImagePreview.src = imageUrl;
                        editImagePreview.classList.remove('d-none');
                    } else {
                        editImagePreview.classList.add('d-none');
                        editImagePreview.removeAttribute('src');
                    }
                    return;
                }

                const deleteBtn = event.target.closest('.js-delete-node');
                if (deleteBtn) {
                    const nodeId = deleteBtn.getAttribute('data-node-id');
                    const title = deleteBtn.getAttribute('data-node-title') || '';
                    document.getElementById('nodeDeleteForm').action = '{{ url('/nodes') }}/' + nodeId;
                    document.getElementById('delete-node-title').textContent = title;
                    document.getElementById('delete-modal-errors').classList.add('d-none');
                }
            });

            const form = document.querySelector('#nodeModal form');
            const modalErrors = document.getElementById('modal-errors');
            const modalElement = document.getElementById('nodeModal');

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                modalErrors.classList.add('d-none');
                modalErrors.textContent = '';

                const formData = new FormData(form);

                try {
                    saveExpandedState();

                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    if (response.status === 422) {
                        const data = await response.json();
                        const messages = Object.values(data.errors || {})
                            .flat()
                            .join(' ');
                        modalErrors.textContent = messages || 'Ошибка валидации.';
                        modalErrors.classList.remove('d-none');
                        return;
                    }

                    if (!response.ok) {
                        modalErrors.textContent = 'Не удалось сохранить узел. Попробуйте еще раз.';
                        modalErrors.classList.remove('d-none');
                        return;
                    }

                    if (window.$ && window.$.fn && window.$.fn.modal) {
                        window.$(modalElement).modal('hide');
                    }

                    form.reset();
                    createImagePreview.classList.add('d-none');
                    createImagePreview.removeAttribute('src');
                    await reloadTree(treeContainer);
                } catch (error) {
                    modalErrors.textContent = 'Сетевая ошибка. Попробуйте еще раз.';
                    modalErrors.classList.remove('d-none');
                }
            });

            const editForm = document.getElementById('nodeEditForm');
            const editErrors = document.getElementById('edit-modal-errors');
            editForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                editErrors.classList.add('d-none');
                editErrors.textContent = '';

                const formData = new FormData(editForm);

                try {
                    saveExpandedState();

                    const response = await fetch(editForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    if (response.status === 422) {
                        const data = await response.json();
                        const messages = Object.values(data.errors || {})
                            .flat()
                            .join(' ');
                        editErrors.textContent = messages || 'Ошибка валидации.';
                        editErrors.classList.remove('d-none');
                        return;
                    }

                    if (!response.ok) {
                        editErrors.textContent = 'Не удалось обновить узел. Попробуйте еще раз.';
                        editErrors.classList.remove('d-none');
                        return;
                    }

                    if (window.$ && window.$.fn && window.$.fn.modal) {
                        window.$('#nodeEditModal').modal('hide');
                    }

                    await reloadTree(treeContainer);
                } catch (error) {
                    editErrors.textContent = 'Сетевая ошибка. Изменения не сохранены.';
                    editErrors.classList.remove('d-none');
                }
            });

            const deleteForm = document.getElementById('nodeDeleteForm');
            const deleteErrors = document.getElementById('delete-modal-errors');
            deleteForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                deleteErrors.classList.add('d-none');
                deleteErrors.textContent = '';

                const formData = new FormData(deleteForm);

                try {
                    saveExpandedState();

                    const response = await fetch(deleteForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    if (response.status === 422) {
                        const data = await response.json();
                        deleteErrors.textContent = data.message || 'Нельзя удалить узел.';
                        deleteErrors.classList.remove('d-none');
                        return;
                    }

                    if (!response.ok) {
                        deleteErrors.textContent = 'Не удалось удалить узел. Попробуйте еще раз.';
                        deleteErrors.classList.remove('d-none');
                        return;
                    }

                    if (window.$ && window.$.fn && window.$.fn.modal) {
                        window.$('#nodeDeleteModal').modal('hide');
                    }

                    await reloadTree(treeContainer);
                } catch (error) {
                    deleteErrors.textContent = 'Сетевая ошибка. Удаление не выполнено.';
                    deleteErrors.classList.remove('d-none');
                }
            });
        });
    </script>
@endcomponent
