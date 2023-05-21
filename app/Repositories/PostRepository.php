<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;
use App\Models\PostTranslation;
use App\Models\Language;
use App\Models\Post;

class PostRepository
{
    /**
     * Get post list with pagination.
     * @param  string $lang Current language prefix
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginate(string $lang): LengthAwarePaginator
    {
        $langId = $this->getLangId($lang);

        $posts = Post::whereHas('translations', function ($query) use ($langId) {
                $query->where('language_id', $langId);
            })
            ->with(['translations' => function ($query) use ($langId) {
                $query->where('language_id', $langId);
            }])
            ->with(['tags' => function ($query) use ($langId) {
                $query->whereHas('translations', function ($query) use ($langId) {
                    $query->where('language_id', $langId);
                })
                ->with(['translations' => function ($query) use ($langId) {
                    $query->where('language_id', $langId);
                }]);
            }]);

        return $posts->paginate(10);
    }

    /**
     * Get post by id.
     * @param  string $lang  Language prefix
     * @param  int    $id    Post id
     * @return \App\Models\Post
     */
    public function getById(string $lang, int $id): Post
    {
        $langId = $this->getLangId($lang);

        $post = Post::whereHas('translations', function ($query) use ($langId) {
                $query->where('language_id', $langId);
            })
            ->with(['translations' => function ($query) use ($langId) {
                $query->where('language_id', $langId);
            }])
            ->with(['tags' => function ($query) use ($langId) {
                $query->whereHas('translations', function ($query) use ($langId) {
                    $query->where('language_id', $langId);
                })
                ->with(['translations' => function ($query) use ($langId) {
                    $query->where('language_id', $langId);
                }]);
            }]);

        return $post->findOrFail($id);
    }

    /**
     * Create new post with translation.
     * @param  string $lang   Current language prefix
     * @param  array  $data   Translations data
     * @return \App\Models\Post
     */
    public function create(string $lang, array $data): Post
    {
        $post = Post::create();

        $translation = new PostTranslation;
        $translation->post_id      = $post->id;
        $translation->language_id  = $this->getLangId($lang);
        $translation->title        = $data['title'];
        $translation->description  = $data['description'];
        $translation->content      = $data['content'];
        $translation->save();

        return $post;
    }

    /**
     * Update post's translations
     * @param  string $lang Current language prefix
     * @param  array  $data Translations data
     * @param  int    $id   Post id
     * @return \App\Models\Post
     */
    public function update(string $lang, array $data, int $id): Post
    {
        $post = Post::findOrFail($id);
        $langId = $this->getLangId($lang);

        $translation = PostTranslation::where('post_id', $post->id)
            ->where('language_id', $langId);

        if ($translation->exists()) {
            $translation->update($data);
        } else {
            $translation = new PostTranslation;
            $translation->post_id      = $post->id;
            $translation->language_id  = $langId;
            $translation->title        = $data['title'];
            $translation->description  = $data['description'];
            $translation->content      = $data['content'];
            $translation->save();
        }

        $post->touch();

        return $post;
    }

    /**
     * Delete post translation and delete post
     * softly if there are no translations.
     * @param  string $lang Language prefix
     * @param  int    $id   Post id
     * @return string       Message string
     */
    public function delete(string $lang, int $id): string
    {
        $post = Post::findOrFail($id);
        $langId = $this->getLangId($lang);

        $translations = PostTranslation::where('post_id', $post->id);
        $languagesNum = $translations->count();
        $translation = $translations->where('language_id', $langId);

        if ($translation->exists()) {
            $translation->delete();
        }

        if ($languagesNum === 1) {
            $post->tags()->detach();
            $post->delete();
            return 'complete';
        }
        return "deleted: {$lang}";
    }

    /**
     * Serch in posts.
     * @param  string $lang Language prefix
     * @param  string $sq   Search query
     * @return \Illuminate\Support\LazyCollection
     */
    public function search(string $lang, string $sq): LazyCollection
    {
        $langId = $this->getLangId($lang);

        $results = PostTranslation::where('language_id', $langId)
            ->where(function ($query) use ($sq) {
                $query->where('title', 'LIKE', "%$sq%")
                    ->orWhere('description', 'LIKE', "%$sq%")
                    ->orWhere('content', 'LIKE', "%$sq%");
            });

        return $results->cursor();
    }

    /**
     * Get language id by prefix.
     * @param  string $lang Language prefix
     * @return int
     */
    protected function getLangId(string $lang): int
    {
        $language = Language::where('prefix', $lang)->first();
        return $language->id ?? 0;
    }

}
