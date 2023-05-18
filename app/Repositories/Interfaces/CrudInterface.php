<?php
namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;
use Illuminate\Http\Response;

interface CrudInterface
{
    /**
     * @param int $page - paginated page, if empty - all data would be returned
     *
     * @param string $sortedBy - how data are sorted, can be combination of fields
     *
     * @param array $filters - how data are filtered
     *
     * returns a filtered data / total rows number / pagination per page
     *
     * @return array
     */
    public function filter(int $page = 1, string $sortedBy = '', array $filters = []): array;

    /**
     * Get an individual model by id
     *
     * @param int $id
     *
     * @return JsonResponse | MessageBag
     */
    public function get(int $id): JsonResponse | MessageBag;

    /**
     * Store new validated model in storage.
     *
     * @return \Illuminate\Routing\Redirector | MessageBag
     */
    public function store(array $data, bool $makeValidation = false): JsonResponse  | MessageBag;

    /**
     * Update validated model with given array in storage
     *
     * @param int $id
     *
     * @param  array $data
     *
     * @return JsonResponse | MessageBag
     */
    public function update(int $id, array $data, bool $makeValidation = false) :  JsonResponse | MessageBag;

    /**
     * Remove the specified Post model from storage
     *
     * @param int $id
     *
     * @return void
     */
    public function delete(int $id) : Response | JsonResponse | MessageBag;
}
