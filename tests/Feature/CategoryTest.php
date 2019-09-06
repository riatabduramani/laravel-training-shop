<?php

namespace Tests\Feature;

use App\Category;
use App\Image;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $user = factory(User::class)->state('admin')->create();

        $this->actingAs($user);
    }

    public function testListingCategories()
    {
        $categories = factory(Category::class, 5)->create();
        $firstCategory = $categories->first();
        $lastCategory = $categories->first();

        $response = $this->get('/categories');

        $response->assertViewIs('categories.index')
            ->assertSeeText($firstCategory->name)
            ->assertSeeText($firstCategory->description)
            ->assertSeeText($lastCategory->name)
            ->assertSeeText($lastCategory->description);
    }

    public function testListingCategoriesWithImage()
    {
        $category = factory(Category::class)->create();
        $image = factory(Image::class)->create([
            'imageable_id' => $category->id,
            'imageable_type' => Category::class,
        ]);

        $response = $this->get('/categories');

        $response->assertViewIs('categories.index')
            ->assertSee($image->path);
    }

    public function testCreatingNewCategory()
    {
        $response = $this->post('/categories', [
            'name' => 'New Category',
            'description' => 'New Description',
        ]);

        $response->assertRedirect('/categories/1');

        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
            'description' => 'New Description',
        ]);
    }

    public function testCreatingNewWithValidationErrors()
    {
        $response = $this->post('/categories', [
            'name' => '',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'description']);

        $this->assertDatabaseMissing('categories', [
            'name' => '',
            'description' => '',
        ]);
    }

    public function testUpdatingExisitingCategory()
    {
        $category = factory(Category::class)->create();

        $response = $this->patch('/categories/' . $category->id, [
            'name' => 'Updated name',
            'description' => 'Updated description',
        ]);

        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated name',
            'description' => 'Updated description',
        ]);
    }

    public function testUpdatingExisitingCategoryWithValidationErrors()
    {
        $category = factory(Category::class)->create();

        $response = $this->patch('/categories/' . $category->id, [
            'name' => 'Updated name',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['description']);

        $this->assertDatabaseHas('categories', [
            'name' => $category->name,
            'description' => $category->description,
        ]);

        $this->assertDatabaseMissing('categories', [
            'name' => 'Updated name',
            'description' => $category->description,
        ]);
    }

    public function testDeletingExisitingCategory()
    {
        $category = factory(Category::class)->create();

        $this->delete('/categories/' . $category->id);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
        ]);
    }

    public function testCreatingNewCategoryAsNonAdmin()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->post('/categories', [
            'name' => 'Lorem',
            'description' => 'Ipsum',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('categories', [
            'name' => 'Lorem',
            'description' => 'Ipsum',
        ]);
    }

    public function testUpdatingExistingCategoryAsNonAdmin()
    {
        $this->actingAs(factory(User::class)->create());

        $category = factory(Category::class)->create();

        $response = $this->patch('/categories/' . $category->id, [
            'name' => 'Lorem',
            'description' => 'Ipsum',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('categories', [
            'name' => $category->name,
            'description' => $category->description,
        ]);

        $this->assertDatabaseMissing('categories', [
            'name' => 'Lorem',
            'description' => 'Ipsum',
        ]);
    }

    public function testDeletingExistingCategoryAsNonAdmin()
    {
        $this->actingAs(factory(User::class)->create());

        $category = factory(Category::class)->create();

        $response = $this->delete('/categories/' . $category->id);

        $response->assertStatus(403);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
        ]);
    }
}
