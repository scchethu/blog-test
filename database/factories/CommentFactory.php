<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
/**
* The name of the factory's corresponding model.
*
* @var string
*/
protected $model = Comment::class;

/**
* Define the model's default state.
*
* @return array
*/
public function definition()
    {
        return [
        'user_id' => User::factory(),
        'post_id' => Post::factory(),
        'parent_id' => null, // Assuming it's a top-level comment
        'body' => $this->faker->paragraph,
        'created_at' => now(),
        'updated_at' => now(),
        ];
    }
}
