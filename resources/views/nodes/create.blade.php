@component('layouts.adminlte', ['title' => 'Создать элемент'])
    <div class="card">
        <div class="card-body">
            <form action="{{ route('nodes.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="title">Название</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        class="form-control"
                        value="{{ old('title') }}"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea
                        id="description"
                        name="description"
                        class="form-control"
                        rows="4"
                    >{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="parent_id">Родительский узел</label>
                    <select id="parent_id" name="parent_id" class="form-control">
                        @if (!$hasRoot)
                            <option value="">Корневой элемент (без родителя)</option>
                        @endif
                        @foreach ($parents as $parent)
                            <option
                                value="{{ $parent->id }}"
                                @selected(old('parent_id', $selectedParentId) == $parent->id)
                            >
                                {{ $parent->title }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">
                        Корневой элемент может быть только один.
                    </small>
                </div>
                <div class="form-group">
                    <label for="sort_order">Порядок сортировки</label>
                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        class="form-control"
                        value="{{ old('sort_order', 0) }}"
                        min="0"
                        required
                    >
                    <small class="form-text text-muted">
                        При раскрытии сначала показывается элемент с меньшим значением сортировки.
                    </small>
                </div>
                <div class="form-group">
                    <label for="image">Изображение</label>
                    <div class="custom-file">
                        <input type="file" id="image" name="image" class="custom-file-input">
                        <label class="custom-file-label" for="image">Выберите файл</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Создать элемент</button>
            </form>
        </div>
    </div>
@endcomponent
