<?php

namespace App\Repositories\Tag;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\TagTranslation;
use App\Models\Language;
use App\Models\Tag;

class TagRepository implements TagRepositoryInterface
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
     * Get tag list with pagination.
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginate(): LengthAwarePaginator
    {
        $tags = Tag::whereHas('translations', function ($query) {
                $query->where('language_id', $this->langId);
            })
            ->with(['translations' => function ($query) {
                $query->where('language_id', $this->langId);
            }]);

        return $tags->paginate(10);
    }

    /**
     * Get tag by id.
     * @param  int  $id  Tag id
     * @return \App\Models\Tag
     */
    public function getById(int $id): Tag
    {
        $tag = Tag::whereHas('translations', function ($query) {
                $query->where('language_id', $this->langId);
            })
            ->with(['translations' => function ($query) {
                $query->where('language_id', $this->langId);
            }]);

        return $tag->findOrFail($id);
    }

    /**
     * Create new tag with title translation.
     * @param  array $data  Tag data
     * @return \App\Models\Tag
     */
    public function create(array $data): Tag
    {
        $tag = Tag::create(['name' => $data['name']]);

        $translation = new TagTranslation;
        $translation->tag_id       = $tag->id;
        $translation->language_id  = $this->langId;
        $translation->title        = $data['title'];
        $translation->save();

        return $tag;
    }

    /**
     * Update tag.
     * @param  int    $id    Tag id
     * @param  array  $data  Tag data
     * @return \App\Models\Tag
     */
    public function update(int $id, array $data): Tag
    {
        $tag = Tag::findOrFail($id);

        $translation = TagTranslation::where('tag_id', $tag->id)
            ->where('language_id', $this->langId);

        if ($translation->exists()) {
            $translation->update(['title' => $data['title']]);
        } else {
            $translation = new TagTranslation;
            $translation->tag_id       = $tag->id;
            $translation->language_id  = $this->langId;
            $translation->title        = $data['title'];
            $translation->save();
        }

        $tag->update(['name' => $data['name']]);

        return $tag;
    }

    /**
     * Delete tag translation or delete tag softly
     * if there are no translations.
     * @param  int  $id  Tag id
     * @return string    Message string
     */
    public function delete(int $id): string
    {
        $tag = Tag::findOrFail($id);

        $translations = TagTranslation::where('tag_id', $tag->id);
        $languagesNum = $translations->count();
        $translation = $translations->where('language_id', $this->langId);

        if ($translation->exists()) {
            $translation->delete();
        }

        $left = $languagesNum - 1;

        if ($left === 0) {
            $tag->delete();
        }

        return "deleted: {$left}";
    }

}
