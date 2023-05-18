<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;

use Tests\TestCase;
use App\Models\Tag;
use Illuminate\Support\Str;

class TagsCrudTest extends TestCase
{
    use InteractsWithExceptionHandling;

    protected static $wasSetup = false;
    protected static $isDebug = false;

    public function setUp(): void
    {
        parent::setUp();
        if ( ! self::$wasSetup) {
            // Regenerate structure / fresh data only once at first test
            Artisan::call(' migrate:fresh --seed');
            Artisan::call('config:clear');
            $databaseName = \DB::connection()->getDatabaseName();
            $result       = Str::endsWith($databaseName, 'HttpTesting');
            if ( ! $result) { // Check valid database for tests
                die('Invalid database "' . $databaseName . '" connected ');
            }
            self::$wasSetup = true;
        }
    } // public function setUp(): void


    // 1) CREATE AND READ TAG
    public function test_1_TagIsAdded()
    {
        // Test Data Setup
        $tagModel = Tag
            ::factory()->make([]);  // model only in memory

        // Test Action
        $response = $this
            ->post(route('tags.store'), $tagModel->toArray());
        $response
            ->assertStatus(HTTP_RESPONSE_OK_RESOURCE_CREATED); // 201

        // READ TAG CREATED ABOVE
        $insertedTag = Tag
            ::getBySearch(search: $tagModel->name, partial: false)
            ->first();

        // Check Assert
        $this->assertNotNull($insertedTag, '11 : Inserted tag not found');
        $this->assertEquals(
            $insertedTag->name,
            $tagModel->name,
            '12 : name read is not equal name on insert'
        );
    } // 1: testTagIsAdded()


    // 2) CREATE / UPDATE AND READ TAG
    public function test_2_TagIsUpdated()
    {
        // Test Data Setup
        $faker = \Faker\Factory::create();
        $tag   = Tag::create(['name' => $faker->text(20)]);

        // Test Action
        $response = $this
            ->put(route('tags.update', $tag->id), [
                'name' => $tag->name . ' UPDATED',
            ]);

        // Check Assert
        $response->assertStatus(HTTP_RESPONSE_OK_RESOURCE_UPDATED);  // 205
        // READ TAG CREATED ABOVE
        $updatedTag = Tag
            ::getBySearch(search: $tag->name . ' UPDATED', partial: false)
            ->first();

        $this->assertNotNull($updatedTag, '21 : updated tag not found');
        $this->assertEquals(
            $updatedTag->name,
            $tag->name . ' UPDATED',
            '22 : Name read is not equal name on update');
    } // 2: TagIsUpdated()


    // 3) CREATE / UPDATE WITH INVALID ID(NEGATIVE) - MUST RAISE VALIDATION ERRORS
    public function test_3_NegativeTagFailuredBeUpdatedAsNotFound()
    {
        // Test Data Setup
        $tag = Tag
            ::factory()->create([]);

        // Test Action
        $response = $this
            ->put(route('tags.update', -$tag->id), [
                'name' => $tag->name . ' UPDATED',
            ]);

        // Check Assert
        $response->assertStatus(HTTP_RESPONSE_NOT_FOUND);  // 404
    } // 3: testNegativeTagFailuredBeUpdatedAsNotFound()


    // 4) CREATE / DELETE TAG
    public function test_4_TagIsDestroyed()
    {
        // Test Data Setup
        $tag = Tag
            ::factory()->create([]);

        // Test Action
        $response = $this
            ->delete(route('tags.destroy', $tag->id), []);

        // Check Assert
        $response->assertStatus(HTTP_RESPONSE_OK_RESOURCE_DELETED);  // 204
    } // 4: testTagIsDestroyed()


    // 5) CREATE / DELETE / RESTORE TAG
    public function test_5_TagIsDestroyedAndRestored()
    {
        // Test Data Setup
        $tag = Tag
            ::factory()->create([]);

        $tag->delete();

        // Test Action
        $response = $this
            ->put(route('tags.restore', $tag->id), []);

        // Check Assert
        $response->assertStatus(HTTP_RESPONSE_OK_RESOURCE_UPDATED);  // 204
    } // 5: testTagIsDestroyedAndRestored()


    // 6) CREATE TAG AND DELETE WITH INVALID ID(NEGATIVE) - MUST RAISE VALIDATION ERRORS
    public function test_6_NegativeTagIsDestroyedAsNotFound()
    {
        // Test Data Setup
        $tag = Tag
            ::factory()->create([]);

        // Test Action
        $response = $this
            ->delete(route('tags.destroy', -$tag->id), []);

        // Check Assert
        $response->assertStatus(HTTP_RESPONSE_NOT_FOUND);  // 404
    } // 6: testTagIsDestroyed()


    // 7) CREATE TAGS AND READ/CHECK THEY ARE ARE FOUND BY NAME FILTERS
    public function test_7_FiltersWithLocale()
    {
        // Test Data Setup
        $faker     = \Faker\Factory::create();
        $tagSearch = 'Test Tag ' . $faker->text(20) . ' Lorem Value';

        // Create 1 new Tag for testing
        Tag::factory()->create(['name' => $tagSearch]);

        // Test Action
        $response = $this
            ->post(route('tags.filter'), [
                'search' => $tagSearch,
            ]);

        // Check Assert
        $response->assertStatus(HTTP_RESPONSE_OK);  // 200
        $this->assertEquals($response->original['tags']->count(), 1, '17 : Number of Tags found invalid');
    } // 7: FiltersWithLocale


    // 8) CREATE / READ TAG
    public function test_8_TagIsShown()
    {
        // Test Data Setup
        $faker = \Faker\Factory::create();
        $tag   = Tag::create(['name' => $faker->text(20)]);

        // Test Action
        $response = $this
            ->get(route('tags.show', $tag->id));

        // Check Assert
        $response->assertStatus(HTTP_RESPONSE_OK);  // 200
        // READ TAG CREATED ABOVE
    } // 8: TagIsShown()


}
