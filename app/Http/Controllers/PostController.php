<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Repositories\PostCrudRepository;
use Illuminate\Support\MessageBag;
use app;

class PostController extends Controller
{

    protected $postCrudRepository;

    public function __construct(PostCrudRepository $postCrudRepository) {
        $this->postCrudRepository             = $postCrudRepository;
    }

    /**
     * Returns (filtered) paginated collection of posts
     *
     * param in POST request : int $page - paginated page, if empty - all data would be returned
     *
     * param in POST request : string $sortedBy - how data are sorted, can be combination of fields
     *
     * param in POST request :  array $filters - how data are filtered, keys : name - string
     *
     * returns filtered data / total rows number / pagination per page
     *
     * @return array : posts - collection of found data,
     * totalPostsCount - total number of found posts,
     * paginationPerPage - number of rows in 1 paginated page
     */
    public function filter(): array
    {
        $request = request();
        return $this->postCrudRepository->filter(
            page: $request->page ?? 1,
            filters: $request->only('page', 'search', 'language_id'),
            sortedBy: $request->sorted_by ?? '',
        );
    }

    /**
     * Get an individual Post model by id
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse | \Illuminate\Support\MessageBag
     */
    public function show(int $id): JsonResponse|MessageBag
    {
        return $this->postCrudRepository->get(id: $id);
    }

    /**
     * Validate and on success to store new post in storage.
     *
     * @return \Illuminate\Http\JsonResponse | \Illuminate\Support\MessageBag
     */
    public function store(Request $request)
    {
        return $this->postCrudRepository->store(data: $request->only('post_id', 'language_id', 'title', 'description', 'content'), makeValidation: true);
    }

    public function update(Request $request, int $postId)
    {
        return $this->postCrudRepository->update(id: $postId, data:
            $request->only('post_id', 'language_id', 'title', 'description', 'content'), makeValidation: true);
    }

    /**
     * Remove the specified Post model from storage.
     *
     * @param int $postId
     *
     * @return \Illuminate\Http\Response | \Illuminate\Http\JsonResponse
     */
    public function destroy(int $postId)
    {
        return $this->postCrudRepository->delete(id: $postId);
    }

    /**
     * Restore priorly trashed specified Post model in storage.
     *
     * @param int $postId
     *
     * @return \Illuminate\Http\Response | \Illuminate\Http\JsonResponse
     */
    public function restore(int $postId)
    {
        return $this->postCrudRepository->restore(id: $postId);
    }

}
