<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\PostRepository;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    /**
     * Constructor
     * @param PostRepository $post
     */
    public function __construct(
        private PostRepository $post
    ) {}

    /**
     * Show post list with pagination.
     * @param  string  $lang  Language prefix
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $lang): JsonResponse
    {
        $posts = $this->post->getPaginate($lang);

        return response()->json($posts);
    }

    /**
     * Show the single post.
     * @param  string  $lang  Language prefix
     * @param  integer $id    Post ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $lang, int $id): JsonResponse
    {
        $post = $this->post->getById($lang, $id);

        return response()->json($post);
    }

    /**
     * Store new post.
     * HTTP Method: POST
     * @param  Request $request
     * @param  string  $lang    Language prefix
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, string $lang): JsonResponse
    {
        $this->validate($request, [
            'translations'             => 'required',
            'translations.title'       => 'required|string|min:3',
            'translations.description' => 'required|string|min:5',
            'translations.content'     => 'required|string|min:6',
            'tags'                     => 'nullable|array',
            'tags.*.id'                => 'required|exists:tags,id',
        ]);

        $translations = $request->input('translations');

        $post = $this->post->create($lang, $translations);

        if ($request->has('tags')) {
            $post->tags()->attach($request->input('tags.*.id'));
        }

        return response()->json($post, 201);
    }

    /**
     * Update the post.
     * HTTP Method: PUT
     * @param  Request $request
     * @param  string  $lang    Language prefix
     * @param  integer $id      Post id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $lang, int $id): JsonResponse
    {
        $this->validate($request, [
            'translations'             => 'required',
            'translations.title'       => 'required|string|min:3',
            'translations.description' => 'required|string|min:5',
            'translations.content'     => 'required|string|min:6',
            'tags'                     => 'nullable|array',
            'tags.*.id'                => 'required|exists:tags,id',
        ]);

        $translations = $request->input('translations');

        $post = $this->post->update($lang, $translations, $id);

        if ($request->has('tags')) {
            $post->tags()->sync($request->input('tags.*.id'));
        } else {
            $post->tags()->detach();
        }

        return response()->json($post);
    }

    /**
     * Destroy the post
     * HTTP Method: DELETE
     * @param  string  $lang   Language prefix
     * @param  integer $id     Post id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $lang, int $id): JsonResponse
    {
        $result = $this->post->delete($lang, $id);

        // return response()->json([$result]);

        return response()->json(null, 204);
    }

    /**
     * Search in post's translations
     * @param  Request $request
     * @param  string  $lang
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, string $lang): JsonResponse
    {
        $sq = $request->input('sq');

        $items = $this->post->search($lang, $sq);

        $output = [];

        if ($items) {
            foreach ($items as $item) {
                $output[] = "/api/{$lang}/posts/{$item->post_id}";
            }
        }

        return response()->json($output);
    }

}
