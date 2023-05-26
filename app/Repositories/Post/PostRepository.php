<?php

namespace App\Repositories\Post;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;
use App\Models\PostTranslation;
use App\Models\Language;
use App\Models\Post;

class PostRepository implements PostRepositoryInterface
{
    /**
     * Constructor
     * @param string $lang
     */
    public function __construct(string $lang)
    {
        $language = Language::where('prefix', $lang)->first();
        $this->langId = $language->id;
    }

    /**
     * Get post list with pagination.
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginate(): LengthAwarePaginator
    {
        $posts = Post::whereHas('translations', function ($query) {
                $query->where('language_id', $this->langId);
            })
            ->with(['translations' => function ($query) {
                $query->where('language_id', $this->langId);
            }])
            ->with(['tags' => function ($query) {
                $query->whereHas('translations', function ($query) {
                    $query->where('language_id', $this->langId);
                })
                ->with(['translations' => function ($query) {
                    $query->where('language_id', $this->langId);
                }]);
            }]);

        return $posts->paginate(10);
    }

    /**
     * Get post by id.
     * @param  int $id  Post id
     * @return \App\Models\Post
     */
    public function getById(int $id): Post
    {
        $post = Post::whereHas('translations', function ($query) {
                $query->where('language_id', $this->langId);
            })
            ->with(['translations' => function ($query) {
                $query->where('language_id', $this->langId);
            }])
            ->with(['tags' => function ($query) {
                $query->whereHas('translations', function ($query) {
                    $query->where('language_id', $this->langId);
                })
                ->with(['translations' => function ($query) {
                    $query->where('language_id', $this->langId);
                }]);
            }]);

        return $post->findOrFail($id);
    }

    /**
     * Create new post with translation.
     * @param  array  $data  Translations data
     * @return \App\Models\Post
     */
    public function create(array $data): Post
    {
        $post = Post::create();

        PostTranslation::create([
            'post_id'     => $post->id,
            'language_id' => $this->langId,
            'title'       => $data['title'],
            'description' => $data['description'],
            'content'     => $data['content'],
        ]);

        return $post;
    }

    /**
     * Update post's translations
     * @param  int    $id   Post id
     * @param  array  $data Translations data
     * @return \App\Models\Post
     */
    public function update(int $id, array $data): Post
    {
        $post = Post::findOrFail($id);

        $translation = PostTranslation::where('post_id', $post->id)
            ->where('language_id', $this->langId);

        if ($translation->exists()) {
            $translation->update($data);
        } else {
            PostTranslation::create([
                'post_id'     => $post->id,
                'language_id' => $this->langId,
                'title'       => $data['title'],
                'description' => $data['description'],
                'content'     => $data['content'],
            ]);
        }

        $post->touch();

        return $post;
    }

    /**
     * Delete post translation and delete post
     * softly if there are no translations.
     * @param  int    $id   Post id
     * @return string       Message string
     */
    public function delete(int $id): string
    {
        $post = Post::findOrFail($id);

        $translations = PostTranslation::where('post_id', $post->id);
        $languagesNum = $translations->count();
        $translation = $translations->where('language_id', $this->langId);

        if ($translation->exists()) {
            $translation->delete();
        }

        $left = $languagesNum - 1;

        if ($left === 0) {
            $post->tags()->detach();
            $post->delete();
        }

        return "deleted: {$left}";
    }

    /**
     * Serch in posts.
     * @param  string $sq   Search query
     * @return \Illuminate\Support\LazyCollection
     */
    public function search(string $sq): LazyCollection
    {
        return PostTranslation::where('language_id', $this->langId)
            ->where(function ($query) use ($sq) {
                $query->where('title', 'LIKE', "%$sq%")
                    ->orWhere('description', 'LIKE', "%$sq%")
                    ->orWhere('content', 'LIKE', "%$sq%");
            })
            ->cursor()
            ->map(function (PostTranslation $item) {
                return [$item->post_id => $item->title];
            });
    }

}
