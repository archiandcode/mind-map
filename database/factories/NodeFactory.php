<?php

namespace Database\Factories;

use App\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Node>
 */
class NodeFactory extends Factory
{
    protected $model = Node::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional(0.7)->paragraphs(2, true),
            'image_path' => null,
            'sort_order' => 0,
            'is_expanded' => false,
        ];
    }
}
