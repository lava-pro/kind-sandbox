<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\CreateTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Repositories\Tag\TagRepositoryInterface;

class TagController extends Controller
{
    /**
     * Constructor
     * @param TagRepositoryInterface $tagRepository
     */
    public function __construct(
        private TagRepositoryInterface $tagRepository
    ) {

    }

    /**
     * Show tag list with pagination.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $tags = $this->tagRepository->getPaginate();

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
        $tag = $this->tagRepository->getById($id);

        return response()->json($tag);
    }

    /**
     * Store new tag.
     * HTTP Method: POST
     * @param  CreateTagRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateTagRequest $request): JsonResponse
    {
        $data = $request->input();

        $tag = $this->tagRepository->create($data);

        return response()->json($tag, 201);
    }

    /**
     * Update tag.
     * HTTP Method: PUT
     * @param  UpdateTagRequest $request
     * @param  string           $lang   Language prefix
     * @param  integer          $id     Tag id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateTagRequest $request, string $lang, int $id): JsonResponse
    {
        $data = $request->input();

        $tag = $this->tagRepository->update($id, $data);

        return response()->json($tag);
    }

    /**
     * Destroy the tag
     * HTTP Method: DELETE
     * @param  string  $lang  Language prefix
     * @param  int     $id    Tag id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $lang, int $id): JsonResponse
    {
        $result = $this->tagRepository->delete($id);

        // return response()->json([$result]);

        return response()->json(null, 204);
    }

}
