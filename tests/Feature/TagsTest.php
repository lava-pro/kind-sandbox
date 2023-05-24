<?php

namespace Tests\Feature;

use App\Models\TagTranslation;
use App\Models\Language;
use App\Models\Tag;
use Tests\TestCase;

class TagsTest extends TestCase
{
    public function test_store_new_tag(): void
    {
         // Create new tag with UA translation
        $response = $this->post(route('tags.store', ['lang' => 'ua']), [
            'name'  => 'first-tag',
            'title' => 'Title for lang: UA',
        ]);
        $response->assertStatus(201);

        // Get the created tag
        $tag = Tag::first();

       // Retrieve the tag for current (UA) version
        $response = $this->get(route('tags.show', ['lang' => 'ua', 'id' => $tag->id]));
        $response->assertStatus(200);
    }

    public function test_retrieving_version_without_translation(): void
    {
        $tag = Tag::first();

       // Attempt retrieving the tag for RU version
        $response = $this->get(route('tags.show', ['lang' => 'ru', 'id' => $tag->id]));
        $response->assertStatus(404);
    }

    public function test_update_tag_with_new_translation(): void
    {
        $tag = Tag::first();

        // Edit tag for RU version
        $response = $this->put(route('tags.update', ['lang' => 'ru', 'id' => $tag->id]), [
            'name'  => 'first-tag',     // Same name string
            'title' => 'Title for lang: RU',
        ]);
        $response->assertStatus(200);

        // Retrieve the tag for RU version
        $response = $this->get(route('tags.show', ['lang' => 'ru', 'id' => $tag->id]));
        $response->assertStatus(200);
    }

    public function test_update_tag_name_field(): void
    {
        $tag = Tag::first();

        // Edit tag for RU version
        $response = $this->put(route('tags.update', ['lang' => 'ru', 'id' => $tag->id]), [
            'name'  => 'first-tag-updated',   // Changed name
            'title' => 'Title for lang: RU',
        ]);
        $response->assertStatus(200);

        $tag = $tag->fresh();

        $this->assertTrue($tag->name == 'first-tag-updated');
    }

    public function test_deleting_tag(): void
    {
        $tag = Tag::first();

        $count = TagTranslation::where('tag_id', $tag->id)->count();

        // Make sure we have two translations
        $this->assertTrue($count == 2);

        // Delete the RU version tag translation
        $response = $this->delete(route('tags.destroy', ['lang' => 'ru', 'id' => $tag->id]));
        $response->assertStatus(204);

        // Attempt Retrieve deleted tag version
        $response = $this->get(route('tags.show', ['lang' => 'ru', 'id' => $tag->id]));
        $response->assertStatus(404);
    }

    public function test_deleting_tag_with_one_translation(): void
    {
        $tag = Tag::first();

        $translation = TagTranslation::where('tag_id', $tag->id);

        // Make sure we only have one translations
        $this->assertTrue($translation->count() == 1);

        // Get translation language prefix
        $item = $translation->with('language')
            ->where('tag_id', $tag->id)
            ->first();

        $prefix = $item->language->prefix;

        // Delete the RU version tag translation
        $response = $this->delete(route('tags.destroy', ['lang' => $prefix, 'id' => $tag->id]));
        $response->assertStatus(204);

        $tag = $tag->fresh();

        // Make sure the tag is soft deleted
        $this->assertTrue($tag->trashed());

        // Attempt Retrieve deleted tag
        $response = $this->get(route('tags.show', ['lang' => $prefix, 'id' => $tag->id]));
        $response->assertStatus(404);

        // Permanently remove tag
        $tag->forceDelete();
    }

}
