<?php
namespace App\Repositories;

use App\Http\Resources\PostResource;
use App\Models\PostTranslation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\MessageBag;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Repositories\Interfaces\CrudInterface;
use Carbon\Carbon;
use DB;
use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use App\Library\PostsSearchResultsIterator;

class PostCrudRepository implements CrudInterface
{
    /*  Post CRUD(implements CrudInterface) BLOCK START */

    /**
     * Returns (filtered) paginated collection of posts
     *
     * @param int $page - paginated page, if empty - all data would be returned
     *
     * @param string $sortedBy - how data are sorted, can be combination of fields
     *
     * @param array $filters - how data are filtered, keys : text - string
     *
     * returns a filtered data / total rows number / pagination per page
     *
     * @return array : Posts - collection of found data,
     * totalPostsCount - total number of found posts,
     * paginationPerPage - number of rows in 1 paginated page
     */
    public function filter(int $page = 1, string $sortedBy = '', array $filters = []): array
    {
        $paginationPerPage = 10;

        $filterSearch = $filters['search'] ?? '';
        $sortByField  = 'id';
        $sortOrdering = 'desc';

        $totalPostsCount = PostTranslation
            ::getBySearch(search: $filterSearch, partial: true)
            ->getByLanguageId($filters['language_id'])
            ->count();
        $posts = PostTranslation
            ::getBySearch(search: $filterSearch, partial: true)
            ->getByLanguageId($filters['language_id'])
            ->orderBy($sortByField, $sortOrdering)
            ->with('post')
            ->with('language')
            ->paginate($paginationPerPage, array('*'), 'page', $page);
        return [
            'posts'             => new PostsSearchResultsIterator($posts),
            'totalPostsCount'   => $totalPostsCount,
            'paginationPerPage' => $paginationPerPage,
        ];
    }

    /**
     * Get an individual Post model by id
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse | \Illuminate\Support\MessageBag
     */
    public function get(int $id): JsonResponse|MessageBag
    {
        $post = Post
            ::getById($id)
            ->firstOrFail();

        return response()->json(['post' => (new PostResource($post))], HTTP_RESPONSE_OK); // 200
    }

    /**
     * Store new validated Post model in storage
     *
     * @return \Illuminate\Http\JsonResponse | \Illuminate\Support\MessageBag
     */
    public function store(array $data, bool $makeValidation = false): JsonResponse|MessageBag
    {
        if ($makeValidation) {
            $postTranslationValidationRulesArray = PostTranslation::getValidationRulesArray(
                postId: null, skipFieldsArray: ['post_id']
            );
            $validator = \Illuminate\Support\Facades\Validator::make($data, $postTranslationValidationRulesArray);
            if ($validator->fails()) {
                $errorMsg = $validator->getMessageBag();

                return $errorMsg;
            }
        } // if ($makeValidation) {

        DB::beginTransaction();
        try {

            $post            = Post::create(['created_at' => Carbon::now(config('app.timezone'))]);
            $postTranslation = PostTranslation::create([
                'post_id'     => $post->id,
                'language_id' => $data['language_id'],
                'title'       => $data['title'],
                'description' => $data['description'],
                'content'     => $data['content']
            ]);
            DB::Commit();
            $post->load('postTranslations.language');

            return response()->json(['post' => (new PostResource($post))],
                HTTP_RESPONSE_OK_RESOURCE_CREATED); // 201
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            DB::rollback();

            return response()->json($e->getMessage(), HTTP_RESPONSE_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update validated Post model with given array in storage
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
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Post "' . $id . '" not found.'], HTTP_RESPONSE_NOT_FOUND); // 404
        }

        if ($makeValidation) {
            $postTranslationValidationRulesArray = PostTranslation::getValidationRulesArray(
                postId: $id,
                skipFieldsArray: ['language_id', 'post_id']
            );
            $validator = \Illuminate\Support\Facades\Validator::make($data, $postTranslationValidationRulesArray);
            if ($validator->fails()) {
                $errorMsg = $validator->getMessageBag();

                return $errorMsg;
            }
        } // if ($makeValidation) {

        DB::beginTransaction();
        try {
            $post->updated_at = Carbon::now(config('app.timezone'));
            $post->save();

            $postTranslation = PostTranslation::updateOrCreate([
                'post_id'     => $post->id,
                'language_id' => $data['language_id'],
            ], [
                'title'       => $data['title'],
                'description' => $data['description'],
                'content'     => $data['content'],
            ]);
            DB::Commit();
            $post->load('postTranslations.language');
            $post->load('postTags.tag');
            return response()->json(['post' => (new PostResource($post))],
                HTTP_RESPONSE_OK_RESOURCE_UPDATED); // 205
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json($e->getMessage(), HTTP_RESPONSE_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified Post model from storage
     *
     * @param int $id
     *
     * @return Response|JsonResponse|MessageBag
     */
    public function delete(int $id): Response|JsonResponse|MessageBag
    {
        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Post "' . $id . '" not found.'], HTTP_RESPONSE_NOT_FOUND); // 404
        }

        DB::beginTransaction();
        try {
            $post->delete();
            DB::commit();

            return response()->noContent();
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json($e->getMessage(), HTTP_RESPONSE_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  Restore priorly trashed specified Post model in storage.
     *
     * @param int $id
     *
     * @return Response|JsonResponse|MessageBag
     */
    public function restore(int $id): Response|JsonResponse|MessageBag
    {
        try {
            $post = Post::withTrashed()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Post "' . $id . '" not found.'], HTTP_RESPONSE_NOT_FOUND); // 404
        }

        DB::beginTransaction();
        try {
            $post->restore();
            DB::commit();

            return response()->json(
                ['post' => (new PostResource($post))],
                HTTP_RESPONSE_OK_RESOURCE_UPDATED
            ); // 205
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json($e->getMessage(), HTTP_RESPONSE_INTERNAL_SERVER_ERROR);
        }
    }


    /*  Post CRUD(implements CrudInterface) BLOCK END */
}

?>
