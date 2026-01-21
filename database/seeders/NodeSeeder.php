<?php

namespace Database\Seeders;

use App\Models\Node;
use Illuminate\Database\Seeder;

class NodeSeeder extends Seeder
{
    public function run(): void
    {
        $root = Node::factory()->state([
            'parent_id' => null,
            'title' => 'Корневой узел',
            'description' => 'Автосгенерированный корень.',
            'sort_order' => 0,
            'is_expanded' => true,
        ])->create();

        $children = Node::factory()
            ->count(6)
            ->sequence(fn ($sequence) => [
                'parent_id' => $root->id,
                'sort_order' => $sequence->index,
            ])
            ->create();

        foreach ($children as $child) {
            Node::factory()
                ->count(mt_rand(2, 5))
                ->sequence(fn ($sequence) => [
                    'parent_id' => $child->id,
                    'sort_order' => $sequence->index,
                ])
                ->create();
        }
    }
}
