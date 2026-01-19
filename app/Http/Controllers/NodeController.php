<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NodeController extends Controller
{
    public function index(): View
    {
        $nodes = Node::query()
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $grouped = $nodes->groupBy(function (Node $node) {
            return $node->parent_id ?? 0;
        });

        return view('nodes.index', [
            'grouped' => $grouped,
        ]);
    }

    public function tree(): View
    {
        $nodes = Node::query()
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $grouped = $nodes->groupBy(function (Node $node) {
            return $node->parent_id ?? 0;
        });

        return view('nodes._tree', [
            'grouped' => $grouped,
        ]);
    }

    public function map(): View
    {
        $nodes = Node::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function (Node $node) {
                return [
                    'id' => $node->id,
                    'title' => $node->title,
                    'description' => $node->description,
                    'parent_id' => $node->parent_id,
                    'sort_order' => $node->sort_order,
                    'is_expanded' => (bool) $node->is_expanded,
                    'image_url' => $node->image_path
                        ? Storage::disk('public')->url($node->image_path)
                        : null,
                ];
            })
            ->values();

        return view('nodes.map', [
            'nodes' => $nodes,
        ]);
    }

    public function create(Request $request): View
    {
        $selectedParentId = $request->integer('parent_id');

        $parents = Node::query()
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('nodes.create', [
            'parents' => $parents,
            'selectedParentId' => $selectedParentId,
        ]);
    }

    public function store(Request $request): Response|RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:nodes,id'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_expanded' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['is_expanded'] = $request->boolean('is_expanded');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('nodes', 'public');
        }

        Node::create($data);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()
            ->route('nodes.index')
            ->with('status', 'Узел создан.');
    }

    public function update(Request $request, Node $node): Response|RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:nodes,id'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_expanded' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['is_expanded'] = $request->boolean('is_expanded');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('nodes', 'public');
        }

        $node->update($data);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()
            ->route('nodes.index')
            ->with('status', 'Узел обновлен.');
    }

    public function destroy(Request $request, Node $node): Response|RedirectResponse|JsonResponse
    {
        if ($node->children()->exists()) {
            $message = 'Нельзя удалить узел с дочерними элементами.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return redirect()->route('nodes.index')->withErrors(['delete' => $message]);
        }

        $node->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()
            ->route('nodes.index')
            ->with('status', 'Узел удален.');
    }
}
