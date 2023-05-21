<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TagTranslation;
use App\Models\Language;
use App\Models\Tag;

class TagRepository
{
    /**
     * Get tag list with pagination.
     * @param  string $lang Current language prefix
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginate(string $lang): LengthAwarePaginator
    {
        $langId = $this->getLangId($lang);

        $tags = Tag::whereHas('translations', function ($query) use ($langId) {
                $query->where('language_id', $langId);
            })
            ->with(['translations' => function ($query) use ($langId) {
                $query->where('language_id', $langId);
            }]);

        return $tags->paginate(10);
    }

    /**
     * Get tag by id.
     * @param  string $lang  Language prefix
     * @param  int    $id    Tag id
     * @return \App\Models\Tag
     */
    public function getById(string $lang, int $id): Tag
    {
        $langId = $this->getLangId($lang);

        $tag = Tag::whereHas('translations', function ($query) use ($langId) {
                $query->where('language_id', $langId);
            })
            ->with(['translations' => function ($query) use ($langId) {
                $query->where('language_id', $langId);
            }]);

        return $tag->findOrFail($id);
    }

    /**
     * Create new tag with title translation.
     * @param  string $lang   Current language prefix
     * @param  array  $data   Tag data
     * @return \App\Models\Tag
     */
    public function create(string $lang, array $data): Tag
    {
        $tag = Tag::create(['name' => $data['name']]);

        $translation = new TagTranslation;
        $translation->tag_id       = $tag->id;
        $translation->language_id  = $this->getLangId($lang);
        $translation->title        = $data['title'];
        $translation->save();

        return $tag;
    }

    /**
     * Update tag.
     * @param  string $lang  Current language prefix
     * @param  array  $data  Tag data
     * @param  int    $id    Tag id
     * @return \App\Models\Tag
     */
    public function update(string $lang, array $data, int $id): Tag
    {
        $tag = Tag::findOrFail($id);

        $langId = $this->getLangId($lang);

        $translation = TagTranslation::where('tag_id', $tag->id)
            ->where('language_id', $langId);

        if ($translation->exists()) {
            $translation->update(['title' => $data['title']]);
        } else {
            $translation = new TagTranslation;
            $translation->tag_id       = $tag->id;
            $translation->language_id  = $langId;
            $translation->title        = $data['title'];
            $translation->save();
        }

        $tag->update(['name' => $data['name']]);

        return $tag;
    }

    /**
     * Delete tag translation or delete tag softly
     * if there are no translations.
     * @param  string $lang Language prefix
     * @param  int    $id   Tag id
     * @return string       Message string
     */
    public function delete(string $lang, int $id): string
    {
        $tag = Tag::findOrFail($id);
        $langId = $this->getLangId($lang);

        $translations = TagTranslation::where('tag_id', $tag->id);
        $languagesNum = $translations->count();
        $translation = $translations->where('language_id', $langId);

        if ($translation->exists()) {
            $translation->delete();
        }

        if ($languagesNum === 1) {
            $tag->delete();
            return 'complete';
        }
        return "deleted: {$lang}";
    }

    /**
     * Get language id by prefix.
     * @param  string $lang Language prefix
     * @return int
     */
    protected function getLangId(string $lang): int
    {
        $language = Language::where('prefix', $lang)->first();
        return $language->id ?: 0;
    }

}
