<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariantTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->token = $this->admin->createToken('test')->plainTextToken;
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}", 'Accept' => 'application/json'];
    }

    // --- Attributes ---

    public function test_can_list_attributes(): void
    {
        $attr = Attribute::create(['name' => 'Boja', 'slug' => 'boja', 'type' => 'color']);
        $attr->values()->create(['value' => 'Crvena', 'color_hex' => '#FF0000']);

        $response = $this->getJson('/api/admin/attributes', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.values.0.value', 'Crvena');
    }

    public function test_can_create_attribute(): void
    {
        $response = $this->postJson('/api/admin/attributes', [
            'name' => 'Veličina',
            'type' => 'select',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('data.slug', 'velicina');
    }

    public function test_can_add_value_to_attribute(): void
    {
        $attr = Attribute::create(['name' => 'Boja', 'slug' => 'boja', 'type' => 'color']);

        $response = $this->postJson("/api/admin/attributes/{$attr->id}/values", [
            'value' => 'Plava',
            'color_hex' => '#0000FF',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('data.value', 'Plava');
    }

    public function test_can_delete_attribute(): void
    {
        $attr = Attribute::create(['name' => 'Test', 'slug' => 'test', 'type' => 'select']);

        $this->deleteJson("/api/admin/attributes/{$attr->id}", [], $this->authHeaders())
            ->assertOk();

        $this->assertDatabaseMissing('attributes', ['id' => $attr->id]);
    }

    // --- Variants ---

    public function test_can_create_variant(): void
    {
        $product = Product::factory()->create();
        $attr = Attribute::create(['name' => 'Boja', 'slug' => 'boja', 'type' => 'color']);
        $val = $attr->values()->create(['value' => 'Crvena', 'color_hex' => '#FF0000']);

        $response = $this->postJson("/api/admin/products/{$product->id}/variants", [
            'sku' => 'PROD-RED',
            'price' => 1500,
            'stock_quantity' => 10,
            'attribute_value_ids' => [$val->id],
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('data.sku', 'PROD-RED')
            ->assertJsonCount(1, 'data.attributes');
    }

    public function test_can_list_variants(): void
    {
        $product = Product::factory()->create();
        $attr = Attribute::create(['name' => 'Boja', 'slug' => 'boja', 'type' => 'color']);
        $val = $attr->values()->create(['value' => 'Crvena']);

        $variant = $product->variants()->create([
            'sku' => 'V1', 'price' => 1000, 'stock_quantity' => 5,
        ]);
        $variant->attributeValues()->attach($val);

        $response = $this->getJson("/api/admin/products/{$product->id}/variants", $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_update_variant(): void
    {
        $product = Product::factory()->create();
        $variant = $product->variants()->create([
            'sku' => 'V1', 'price' => 1000, 'stock_quantity' => 5,
        ]);

        $response = $this->putJson("/api/admin/variants/{$variant->id}", [
            'price' => 1200,
            'stock_quantity' => 20,
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.price', '1200.00');
    }

    public function test_can_delete_variant(): void
    {
        $product = Product::factory()->create();
        $variant = $product->variants()->create([
            'sku' => 'V-DEL', 'stock_quantity' => 0,
        ]);

        $this->deleteJson("/api/admin/variants/{$variant->id}", [], $this->authHeaders())
            ->assertOk();

        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }

    public function test_can_bulk_update_variants(): void
    {
        $product = Product::factory()->create();
        $v1 = $product->variants()->create(['sku' => 'B1', 'price' => 100, 'stock_quantity' => 1]);
        $v2 = $product->variants()->create(['sku' => 'B2', 'price' => 200, 'stock_quantity' => 2]);

        $response = $this->putJson("/api/admin/products/{$product->id}/variants/bulk", [
            'variants' => [
                ['id' => $v1->id, 'price' => 150, 'stock_quantity' => 10],
                ['id' => $v2->id, 'price' => 250, 'stock_quantity' => 20],
            ],
        ], $this->authHeaders());

        $response->assertOk();
        $this->assertEquals('150.00', $v1->fresh()->price);
        $this->assertEquals(20, $v2->fresh()->stock_quantity);
    }

    // --- Public ---

    public function test_public_product_includes_variants(): void
    {
        $product = Product::factory()->create(['is_active' => true]);
        $attr = Attribute::create(['name' => 'Boja', 'slug' => 'boja', 'type' => 'color']);
        $val = $attr->values()->create(['value' => 'Crvena', 'color_hex' => '#FF0000']);

        $variant = $product->variants()->create([
            'sku' => 'PUB-V1', 'price' => 1000, 'stock_quantity' => 5, 'is_active' => true,
        ]);
        $variant->attributeValues()->attach($val);

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.variants.0.sku', 'PUB-V1')
            ->assertJsonPath('data.variants.0.attributes.0.value', 'Crvena');
    }
}
