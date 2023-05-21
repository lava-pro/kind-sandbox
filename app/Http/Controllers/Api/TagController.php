<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\TagRepository;
use App\Http\Controllers\Controller;

class TagController extends Controller
{
    /**
     * Constructor
     * @param TagRepository $tag
     */
    public function __construct(
        private TagRepository $tag
    ) {}

    /**
     * Show tag list with pagination.
     * @param  string  $lang  Language prefix
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $lang): JsonResponse
    {
        $tags = $this->tag->getPaginate($lang);

        return response()->json($tags);
    }

    /**
     * Show the single tag.
     * @param  string  $lang  Language prefix
     * @param  integer $id    Tag id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $lang, int $id): JsonResponse
    {
        $tag = $this->tag->getById($lang, $id);

        return response()->json($tag);
    }

    /**
     * Store new tag.
     * HTTP Method: POST
     * @param  Request $request
     * @param  string  $lang    Language prefix
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, string $lang): JsonResponse
    {
        $this->validate($request, [
            'name'  => 'required|string|min:3|max:32|unique:tags,name',
            'title' => 'required|string|min:3|max:64',
        ]);

        $data = $request->input();

        $tag = $this->tag->create($lang, $data);

        return response()->json($tag, 201);
    }

    /**
     * Update tag.
     * HTTP Method: PUT
     * @param  Request $request
     * @param  string  $lang    Language prefix
     * @param  integer $id      Tag id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $lang, int $id): JsonResponse
    {
        $this->validate($request, [
            'name'  => 'required|string|min:3|max:32',
            'title' => 'required|string|min:3|max:64',
        ]);

        $data = $request->input();

        $tag = $this->tag->update($lang, $data, $id);

        return response()->json($tag);
    }

    /**
     * Destroy the tag
     * HTTP Method: DELETE
     * @param  string  $lang   Language prefix
     * @param  int     $id     Tag id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $lang, int $id): JsonResponse
    {
        $result = $this->tag->delete($lang, $id);

        // return response()->json([$result]);

        return response()->json(null, 204);
    }

}
