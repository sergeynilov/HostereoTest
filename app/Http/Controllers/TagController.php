<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Repositories\TagCrudRepository;
use Illuminate\Support\MessageBag;
use app;

class TagController extends Controller
{

    protected $tagCrudRepository;

    public function __construct(TagCrudRepository $tagCrudRepository) {
        $this->tagCrudRepository             = $tagCrudRepository;
    }

    /**
     * Returns (filtered) paginated collection of tags
     *
     * param in POST request : int $page - paginated page, if empty - all data would be returned
     *
     * param in POST request : string $sortedBy - how data are sorted, can be combination of fields
     *
     * param in POST request : array $filters - how data are filtered, keys : name - string
     *
     * returns filtered data / total rows number / pagination per page
     *
     * @return array : tags - collection of found data,
     * totalTagsCount - total number of found tags,
     * paginationPerPage - number of rows in 1 paginated page
     */
    public function filter(): array
    {
        $request = request();
        return $this->tagCrudRepository->filter(
            page: $request->page ?? 1,
            filters: $request->only('page', 'search'),
            sortedBy: $request->sorted_by ?? '',
        );
    }

    /**
     * Get an individual Tag model by id
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse | \Illuminate\Support\MessageBag
     */
//    public function get(int $id): JsonResponse | MessageBag
    public function show(int $id): JsonResponse|MessageBag
    {
        return $this->tagCrudRepository->get(id: $id);
    }

    /**
     * Validate and on success to store new tag in storage.
     *
     * @return \Illuminate\Http\JsonResponse | \Illuminate\Support\MessageBag
     */
    public function store(Request $request)
    {
        return $this->tagCrudRepository->store(data: $request->only('name'), makeValidation: true);
    }

    public function update(Request $request, int $tagId)
    {
        return $this->tagCrudRepository->update(id: $tagId, data:
            $request->only('name'), makeValidation: true);
    }

    /**
     * Remove the specified Tag model from storage.
     *
     * @param int $tagId
     *
     * @return \Illuminate\Http\Response | \Illuminate\Http\JsonResponse
     */
    public function destroy(int $tagId)
    {
        return $this->tagCrudRepository->delete(id: $tagId);
    }

    /**
     * Restore priorly trashed specified Tag model in storage.
     *
     * @param int $tagId
     *
     * @return \Illuminate\Http\Response | \Illuminate\Http\JsonResponse
     */
    public function restore(int $tagId)
    {
        return $this->tagCrudRepository->restore(id: $tagId);
    }

}
