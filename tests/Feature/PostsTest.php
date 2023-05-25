<?php

namespace Tests\Feature;

use App\Models\PostTranslation;
use App\Models\TagTranslation;
use App\Models\Language;
use App\Models\Post;
use App\Models\Tag;
use Tests\TestCase;

class PostsTest extends TestCase
{
    public function test_store_new_post(): void
    {
        // Create tags for use with posts.
        $this->insertNewTags();

        // Get tag for first post.
        $tag = Tag::where('name', 'Tag: 1')->first();

        // Create new post with UA translation
        $response = $this->post(route('posts.store', ['lang' => 'ua']), [
            'translations' => [
                'title'       => "Title for: UA",
                'description' => "Description for: UA",
                'content'     => "Content for: UA",
            ],
            'tags' => [
                ['id' => $tag->id],
            ],
        ]);
        $response->assertStatus(201);

        // Get the created post
        $post = Post::first();

        // Retrieving post
        $response = $this->get(route('posts.show', ['lang' => 'ua', 'id' => $post->id]));
        $response->assertStatus(200);
    }

    public function test_retrieving_post_without_translation(): void
    {
        $post = Post::first();

       // Attempt retrieving the post for RU version
        $response = $this->get(route('posts.show', ['lang' => 'ru', 'id' => $post->id]));
        $response->assertStatus(404);
    }

    public function test_update_post_with_new_translation(): void
    {
        $post = Post::first();

        // Update post for RU version
        $response = $this->put(route('posts.update', ['lang' => 'ru', 'id' => $post->id]), [
            'translations' => [
                'title'       => "Title for: RU",
                'description' => "Description for: RU",
                'content'     => "Content for: RU",
            ],
            'tags' => [
            ],
        ]);
        $response->assertStatus(200);

        // Retrieve the post for RU version
        $response = $this->get(route('posts.show', ['lang' => 'ru', 'id' => $post->id]));
        $response->assertStatus(200);
    }

    public function test_deleting_post(): void
    {
        $post = Post::first();

        $count = PostTranslation::where('post_id', $post->id)->count();

        // Make sure we have two translations for this post
        $this->assertTrue($count == 2);

        // Delete the RU version post translation
        $response = $this->delete(route('posts.destroy', ['lang' => 'ru', 'id' => $post->id]));
        $response->assertStatus(204);

        // Attempt Retrieve deleted post version
        $response = $this->get(route('posts.show', ['lang' => 'ru', 'id' => $post->id]));
        $response->assertStatus(404);
    }

    public function test_deleting_post_with_one_translation(): void
    {
        $post = Post::first();

        $translation = PostTranslation::where('post_id', $post->id);

        // Make sure we only have one translations for this post
        $this->assertTrue($translation->count() == 1);

        // Get translation language prefix
        $item = $translation->with('language')
            ->where('post_id', $post->id)
            ->first();

        $prefix = $item->language->prefix;

        // Delete the RU version post translation
        $response = $this->delete(route('posts.destroy', ['lang' => $prefix, 'id' => $post->id]));
        $response->assertStatus(204);

        $post = $post->fresh();

        // Make sure the post is soft deleted
        $this->assertTrue($post->trashed());

        // Attempt retrieve the soft deleted post
        $response = $this->get(route('posts.show', ['lang' => $prefix, 'id' => $post->id]));
        $response->assertStatus(404);
    }

    private function insertNewTags(int $count = 1)
    {
        $prefixes = ['ua', 'ru', 'en'];

        $i = 1;
        while ($i <= $count) {
            $tag = Tag::create(['name' => "Tag: {$i}"]);
            foreach ($prefixes as $prefix) {
                $language = Language::where('prefix', $prefix)->first();
                $translation = new TagTranslation;
                $translation->tag_id       = $tag->id;
                $translation->language_id  = $language->id;
                $translation->title        = "Title for: {$prefix}";
                $translation->save();
            }
            $i++;
        }
    }

}
