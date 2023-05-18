<?php
namespace App\Repositories;

use App\Http\Resources\TagResource;
use Illuminate\Support\MessageBag;
use App\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Repositories\Interfaces\CrudInterface;
use Carbon\Carbon;
use DB;
use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use App\Library\TagsSearchResultsIterator;

class TagCrudRepository implements CrudInterface
{
    /*  Tag CRUD(implements CrudInterface) BLOCK START */

    /**
     * Returns (filtered) paginated collection of tags
     *
     * @param int $page - paginated page, if empty - all data would be returned
     *
     * @param string $sortedBy - how data are sorted, can be combination of fields
     *
     * @param array $filters - how data are filtered, keys : name - string
     *
     * returns a filtered data / total rows number / pagination per page
     *
     * @return array : Tags - collection of found data,
     * totalTagsCount - total number of found tags,
     * paginationPerPage - number of rows in 1 paginated page
     */
    public function filter(int $page = 1, string $sortedBy = '', array $filters = []): array
    {
        $paginationPerPage = 10;

        $filterSearch = $filters['search'] ?? '';
        $sortByField  = 'id';
        $sortOrdering = 'desc';

        $totalTagsCount = Tag
            ::getBySearch(search: $filterSearch, partial: true)
            ->count();
        $tags = Tag
            ::getBySearch(search: $filterSearch, partial: true)
            ->orderBy($sortByField, $sortOrdering)
            ->with('postTags')
            ->paginate($paginationPerPage, array('*'), 'page', $page);

        return [
            'tags'             => new TagsSearchResultsIterator($tags),
            'totalTagsCount'   => $totalTagsCount,
            'paginationPerPage' => $paginationPerPage,
        ];
    }

    /**
     * Get an individual Tag model by id
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse | \Illuminate\Support\MessageBag
     */
    public function get(int $id): JsonResponse|MessageBag
    {
        $tag = Tag
            ::getById($id)
            ->firstOrFail();

        return response()->json(['tag' => (new TagResource($tag))], HTTP_RESPONSE_OK); // 200
    }

    /**
     * Store new validated Tag model in storage
     *
     * @return \Illuminate\Http\JsonResponse | \Illuminate\Support\MessageBag
     */
    public function store(array $data, bool $makeValidation = false): JsonResponse|MessageBag
    {
        if ($makeValidation) {
            $tagValidationRulesArray = Tag::getValidationRulesArray(
                tagId: null
            );
            $validator = \Illuminate\Support\Facades\Validator::make($data, $tagValidationRulesArray);
            if ($validator->fails()) {
                $errorMsg = $validator->getMessageBag();

                return $errorMsg;
            }
        } // if ($makeValidation) {

        DB::beginTransaction();
        try {

            $tag = Tag::create([
                'name' => $data['name'],
            ]);
            DB::Commit();

            return response()->json(['tag' => (new TagResource($tag))],
                HTTP_RESPONSE_OK_RESOURCE_CREATED); // 201
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            echo '<pre>::$errorMessage::' . print_r($errorMessage, true) . '</pre>';
            DB::rollback();

            return response()->json($e->getMessage(), HTTP_RESPONSE_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update validated Tag model with given array in storage
     *
     * @param int $id
     *
     * @param array $data
     *
     * @return JsonResponse|MessageBag
     */
    public function update(int $id, array $data, bool $makeValidation = false): JsonResponse|MessageBag
    {
        try {
            $tag = Tag::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Tag "' . $id . '" not found.'], HTTP_RESPONSE_NOT_FOUND); // 404
        }

        if ($makeValidation) {
            $tagValidationRulesArray = Tag::getValidationRulesArray(
                tagId: $id
            );
            $validator = \Illuminate\Support\Facades\Validator::make($data, $tagValidationRulesArray);
            if ($validator->fails()) {
                $errorMsg = $validator->getMessageBag();

                return $errorMsg;
            }
        } // if ($makeValidation) {

        $data['updated_at'] = Carbon::now(config('app.timezone'));
        DB::beginTransaction();
        try {
            $tag->update($data);
            DB::Commit();

            return response()->json(
                ['tag' => (new TagResource($tag))],
                HTTP_RESPONSE_OK_RESOURCE_UPDATED
            ); // 205
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(), HTTP_RESPONSE_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified Tag model from storage
     *
     * @param int $id
     *
     * @return Response|JsonResponse|MessageBag
     */
    public function delete(int $id): Response|JsonResponse|MessageBag
    {
        try {
            $tag = Tag::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Tag "' . $id . '" not found.'], HTTP_RESPONSE_NOT_FOUND); // 404
        }

        DB::beginTransaction();
        try {
            $tag->delete();
            DB::commit();

            return response()->noContent();
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json($e->getMessage(), HTTP_RESPONSE_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  Restore priorly trashed specified Tag model in storage.
     *
     * @param int $id
     *
     * @return Response|JsonResponse|MessageBag
     */
    public function restore(int $id): Response|JsonResponse|MessageBag
    {
        try {
            $tag = Tag::withTrashed()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Tag "' . $id . '" not found.'], HTTP_RESPONSE_NOT_FOUND); // 404
        }

        DB::beginTransaction();
        try {
            $tag->restore();
            DB::commit();

            return response()->json(
                ['tag' => (new TagResource($tag))],
                HTTP_RESPONSE_OK_RESOURCE_UPDATED
            ); // 205
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json($e->getMessage(), HTTP_RESPONSE_INTERNAL_SERVER_ERROR);
        }
    }

    /*  Tag CRUD(implements CrudInterface) BLOCK END */
}

?>
