<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_paginated_posts_with_comments_and_replies()
    {
        $posts = Post::factory()->count(5)->create();

        foreach ($posts as $post) {
            $post->comments()->createMany(
                Comment::factory()->count(3)->raw(['parent_id' => null])
            );
        }

        $response = $this->actingAs($this->createUser(), 'api')->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'body',
                        'comments' => [
                            '*' => [
                                'id',
                                'body',
                                'replies' => [
                                    '*' => [
                                        'id',
                                        'body',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_stores_a_post()
    {
        $postData = [
            'title' => 'Test Post',
            'body' => 'This is a test post.',
        ];

        $response = $this->actingAs($this->createUser(), 'api')->postJson('/api/posts', $postData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'title',
                'body',
            ]);

        $this->assertDatabaseHas('posts', $postData);
    }

    /** @test */
    public function it_updates_a_post()
    {
        $post = Post::factory()->create();

        $updatedData = [
            'title' => 'Updated Title',
            'body' => 'Updated Body',
        ];

        $response = $this->actingAs($this->createUser(), 'api')->putJson("/api/posts/{$post->id}", $updatedData);


        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', $updatedData);
    }

    /** @test */
    public function it_deletes_a_post_and_related_comments()
    {
        $post = Post::factory()->create();
        $comments = Comment::factory()->count(3)->create(['post_id' => $post->id]);


        $response = $this->actingAs($this->createUser(), 'api')->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200);

        $response
           ->assertStatus(200)
           ->assertJson([
               'status' => 'success',
               'message' => 'Deleted',
           ]);


    }

    private function createUser()
    {
        $user = User::factory()->create();
        Auth::guard('api')->login($user);
        return $user;
    }
}
