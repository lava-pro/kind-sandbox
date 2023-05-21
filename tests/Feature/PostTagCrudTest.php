<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\PostTranslation;
use App\Models\TagTranslation;
use App\Models\Language;
use App\Models\Post;
use App\Models\Tag;

class PostTagCrudTest extends TestCase
{
    use RefreshDatabase;

    public function testPostTagCrud()
    {
       // Languages data
        $languages = [
            ['prefix' => 'ua', 'locale' => 'Ukrainian'],
            ['prefix' => 'ru', 'locale' => 'Russian'],
            ['prefix' => 'en', 'locale' => 'English'],
        ];

        // Insert the languages
        Language::insert($languages);

        $this->assertTrue(Language::count() == 3);

        //
        // Tag inserting...
        //

        // Create new tag with UA translation
        $response = $this->post("/api/ua/tags", [
            'name'  => 'first-tag',
            'title' => 'Title for: UA',
        ]);

        $response->assertStatus(201);

        // Get the created tag
        $tag = Tag::first();

        // Test the number of traslations for current tag
        $numberTranslations = TagTranslation::where('tag_id', $tag->id)
            ->count();

        $this->assertTrue($numberTranslations == 1);

        //
        // Tag Retrieving...
        //

        $response = $this->get("/api/ua/tags/{$tag->id}");

        $response->assertStatus(200);

        $response = $this->get("/api/ru/tags/{$tag->id}");

        $response->assertStatus(404);

        $response = $this->get("/api/en/tags/{$tag->id}");

        $response->assertStatus(404);

        //
        // Tag Editing...
        //

        // Edit tag for RU version
        $response = $this->put("/api/ru/tags/{$tag->id}", [
            'name'  => 'first-tag',    // Same name string
            'title' => 'Title for: RU',
        ]);

        $response->assertStatus(200);

        $response = $this->get("/api/ru/tags/{$tag->id}");

        $response->assertStatus(200);

        // Edit tag for EN version
        $response = $this->put("/api/en/tags/{$tag->id}", [
            'name'  => 'first-tag',    // Same name string
            'title' => 'Title for: EN',
        ]);

        $response->assertStatus(200);

        $response = $this->get("/api/en/tags/{$tag->id}");

        $response->assertStatus(200);

        // Test the number of traslations for current tag
        $count = TagTranslation::where('tag_id', $tag->id)
            ->count();

        $this->assertTrue($count == 3);

        // Edit the tag name in eny version (ezample ua)
        $response = $this->put("/api/ua/tags/{$tag->id}", [
            'name'  => 'first-tag-changed', // Change name
            'title' => 'Title for: UA',
        ]);

        $response->assertStatus(200);

        $tag = $tag->fresh();

        $this->assertTrue($tag->name == 'first-tag-changed');

        //
        // Post inserting...
        //

        $firstTag = Tag::where('name', 'first-tag-changed')
            ->first();

        // Create new post with UA translation
        $response = $this->post("/api/ua/posts", [
            'translations' => [
                'title'       => "Title for: UA",
                'description' => "Description for: UA",
                'content'     => "Content for: UA",
            ],
            'tags' => [
                ['id' => $firstTag->id],
            ],
        ]);

        $response->assertStatus(201);

        // Get the created post
        $post = Post::first();

        // Test the number of traslations for current post
        $numberTranslations = PostTranslation::where('post_id', $post->id)
            ->count();

        $this->assertTrue($numberTranslations == 1);

        // Test the number of tags for current post
        $numberTags = $post->tags->count();

        $this->assertTrue($numberTags == 1);

        //
        // Post Retrieving...
        //

        $response = $this->get("/api/ua/posts/{$post->id}");

        $response->assertStatus(200);

        $response = $this->get("/api/ru/posts/{$post->id}");

        $response->assertStatus(404);

        $response = $this->get("/api/en/posts/{$post->id}");

        $response->assertStatus(404);

        //
        // Post Editing...
        //

        // Edit post for RU version
        $response = $this->put("/api/ru/posts/{$post->id}", [
            'translations' => [
                'title'       => "Title for: RU",
                'description' => "Description for: RU",
                'content'     => "Content for: RU",
            ],
            'tags' => [],
        ]);

        $response->assertStatus(200);

        // Test for adding new translation
        $lng = Language::where('prefix', 'ru')->first();
        $translation = PostTranslation::where('post_id', $post->id)
            ->where('language_id', $lng->id)
            ->first();

        $this->assertTrue($translation->title == 'Title for: RU');

        $response = $this->get('/api/ru/posts/'. $post->id);

        $response->assertStatus(200);

        // Edit post for EN version
        $response = $this->put("/api/en/posts/{$post->id}", [
            'translations' => [
                'title'       => "Title for: EN",
                'description' => "Description for: EN",
                'content'     => "Content for: EN",
            ],
            'tags' => [],
        ]);

        $response->assertStatus(200);

        // Test the number of traslations for current post
        $count = PostTranslation::where('post_id', $post->id)
            ->count();

        $this->assertTrue($count == 3);

        // Edit post for UA version with exists translates
        $response = $this->put("/api/ua/posts/{$post->id}", [
            'translations' => [
                'title'       => "Edited title for: UA",
                'description' => "Description for: UA",
                'content'     => "Content for: UA",
            ],
            'tags' => [],
        ]);

        $response->assertStatus(200);

        // Test for update exist translation
        $lng = Language::where('prefix', 'ua')->first();
        $translation = PostTranslation::where('post_id', $post->id)
            ->where('language_id', $lng->id)
            ->first();

        $this->assertTrue($translation->title == 'Edited title for: UA');

        $post = $post->fresh();

        // Test the number of tags after post editing
        $numberTags = $post->tags->count();

        $this->assertTrue($numberTags == 0);

        //
        // Post deleting...
        //

        // Delete the EN version post
        $response = $this->delete("/api/en/posts/{$post->id}");

        $response->assertStatus(204);

        // Attempt Retrieve deleted post
        $response = $this->get("/api/en/posts/{$post->id}");

        $response->assertStatus(404);

        // Delete the RU version post
        $response = $this->delete("/api/ru/posts/{$post->id}");

        $response->assertStatus(204);

        // Attempt Retrieve deleted post
        $response = $this->get("/api/ru/posts/{$post->id}");

        $response->assertStatus(404);

        // Delete the last version post (ua)
        $response = $this->delete("/api/ua/posts/{$post->id}");

        $response->assertStatus(204);

        $post = $post->fresh();

        // Test the post soft deleting
        $this->assertTrue($post->trashed());

        //
        // Tag deleting...
        //

        // Delete the EN version tag
        $response = $this->delete("/api/en/tags/{$tag->id}");

        $response->assertStatus(204);

        // Attempt Retrieve deleted tag
        $response = $this->get("/api/en/tags/{$tag->id}");

        $response->assertStatus(404);

        // Delete the RU version tag
        $response = $this->delete("/api/ru/tags/{$tag->id}");

        $response->assertStatus(204);

        // Attempt Retrieve deleted tag
        $response = $this->get("/api/ru/tags/{$tag->id}");

        $response->assertStatus(404);

        // Delete the last version tag (ua)
        $response = $this->delete("/api/ua/tags/{$tag->id}");

        $response->assertStatus(204);

        $tag = $tag->fresh();

        // Test the tag soft deleting
        $this->assertTrue($tag->trashed());

    }
}

