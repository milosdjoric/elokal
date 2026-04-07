<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(Admin::factory()->superAdmin()->create(), ['*']);
        Storage::fake('public');
        Queue::fake();

        $this->product = Product::factory()->create();
    }

    public function test_can_upload_image(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $response = $this->postJson("/api/admin/products/{$this->product->id}/images", [
            'image' => $file,
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['id', 'image_path', 'is_primary']);

        $this->assertDatabaseCount('product_images', 1);
    }

    public function test_first_image_is_automatically_primary(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->postJson("/api/admin/products/{$this->product->id}/images", [
            'image' => $file,
        ]);

        $response->assertJsonPath('is_primary', true);
    }

    public function test_can_set_image_as_primary(): void
    {
        ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'is_primary' => true,
        ]);

        $file = UploadedFile::fake()->image('photo2.jpg');

        $response = $this->postJson("/api/admin/products/{$this->product->id}/images", [
            'image' => $file,
            'is_primary' => true,
        ]);

        $response->assertJsonPath('is_primary', true);

        // Prethodna primary slika je resetovana
        $this->assertDatabaseCount('product_images', 2);
        $this->assertEquals(1, $this->product->images()->where('is_primary', true)->count());
    }

    public function test_upload_validates_file_type(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson("/api/admin/products/{$this->product->id}/images", [
            'image' => $file,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_upload_validates_file_size(): void
    {
        $file = UploadedFile::fake()->image('huge.jpg')->size(6000);

        $response = $this->postJson("/api/admin/products/{$this->product->id}/images", [
            'image' => $file,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_can_delete_image(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'image_path' => 'products/test.jpg',
        ]);

        Storage::disk('public')->put('products/test.jpg', 'content');

        $response = $this->deleteJson(
            "/api/admin/products/{$this->product->id}/images/{$image->id}"
        );

        $response->assertOk();
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    }

    public function test_deleting_primary_promotes_next_image(): void
    {
        $primary = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'is_primary' => true,
            'sort_order' => 0,
            'image_path' => 'products/primary.jpg',
        ]);

        $second = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        Storage::disk('public')->put('products/primary.jpg', 'content');

        $this->deleteJson("/api/admin/products/{$this->product->id}/images/{$primary->id}");

        $this->assertTrue($second->fresh()->is_primary);
    }

    public function test_can_reorder_images(): void
    {
        $img1 = ProductImage::factory()->create(['product_id' => $this->product->id, 'sort_order' => 0]);
        $img2 = ProductImage::factory()->create(['product_id' => $this->product->id, 'sort_order' => 1]);
        $img3 = ProductImage::factory()->create(['product_id' => $this->product->id, 'sort_order' => 2]);

        $response = $this->patchJson("/api/admin/products/{$this->product->id}/images/reorder", [
            'order' => [$img3->id, $img1->id, $img2->id],
        ]);

        $response->assertOk();

        $this->assertEquals(0, $img3->fresh()->sort_order);
        $this->assertEquals(1, $img1->fresh()->sort_order);
        $this->assertEquals(2, $img2->fresh()->sort_order);
    }

    public function test_cannot_delete_image_from_another_product(): void
    {
        $otherProduct = Product::factory()->create();
        $image = ProductImage::factory()->create([
            'product_id' => $otherProduct->id,
        ]);

        $response = $this->deleteJson(
            "/api/admin/products/{$this->product->id}/images/{$image->id}"
        );

        $response->assertNotFound();
    }
}
