<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Post\PostRepositoryInterface;

class PostController extends Controller
{
    /**
     * Constructor
     * @param  PostRepositoryInterface $postRepository
     */
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {

    }

    /**
     * Show post list with pagination.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $posts = $this->postRepository->getPaginate();

        return response()->json($posts);
    }

    /**
     * Show the single post.
     * @param  integer  $id  Post id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $lang, int $id): JsonResponse
    {
        $post = $this->postRepository->getById($id);

        return response()->json($post);
    }

    /**
     * Store new post.
     * HTTP Method: POST
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
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

        $post = $this->postRepository->create($translations);

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

        $post = $this->postRepository->update($id, $translations);

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
     * @param  string  $lang  Language prefix
     * @param  integer $id    Post id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $lang, int $id): JsonResponse
    {
        $result = $this->postRepository->delete($id);

        // return response()->json([$result]);

        return response()->json(null, 204);
    }

    /**
     * Search in post's translations
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $sq = $request->input('sq');

        $items = $this->postRepository->search($sq);

        return response()->json($items);
    }

}
