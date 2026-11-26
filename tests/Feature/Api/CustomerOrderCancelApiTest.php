<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerOrderCancelApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_cancel_pending_order_and_stock_is_returned(): void
    {
        $customer = User::create([
            'name' => 'Pelanggan',
            'email' => 'customer@example.com',
            'password' => 'password123',
            'role' => 'customer',
        ]);

        Sanctum::actingAs($customer, ['customer']);

        $product = $this->createProduct();
        $order = $this->createOrder($customer, $product, 'pending');

        $response = $this->patchJson(
            "/api/v1/orders/{$order->id}/cancel"
        );

        $response->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertSame(10, $product->fresh()->stock);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'type' => 'IN',
            'quantity' => 2,
            'reference' => 'CANCEL-'.$order->code,
        ]);
    }

    public function test_customer_cannot_cancel_confirmed_order(): void
    {
        $customer = User::create([
            'name' => 'Pelanggan',
            'email' => 'customer@example.com',
            'password' => 'password123',
            'role' => 'customer',
        ]);

        Sanctum::actingAs($customer, ['customer']);

        $product = $this->createProduct();
        $order = $this->createOrder($customer, $product, 'confirmed');

        $response = $this->patchJson(
            "/api/v1/orders/{$order->id}/cancel"
        );

        $response->assertStatus(409);

        $this->assertSame(8, $product->fresh()->stock);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseMissing('stock_movements', [
            'reference' => 'CANCEL-'.$order->code,
        ]);
    }

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        return Product::create([
            'category_id' => $category->id,
            'sku' => 'SKU-TEST',
            'name' => 'Mouse Wireless',
            'slug' => 'mouse-wireless',
            'description' => 'Produk contoh',
            'price' => 5000,
            'stock' => 8,
            'is_active' => true,
        ]);
    }

    private function createOrder(
        User $customer,
        Product $product,
        string $status
    ): Order {
        $order = Order::create([
            'code' => 'ORD-TEST-001',
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'phone' => '081234567890',
            'address' => 'Purwokerto',
            'shipping_courier' => 'JNE',
            'shipping_service' => 'REG',
            'shipping_eta' => '2-3 hari',
            'subtotal' => 10000,
            'shipping_cost' => 18000,
            'grand_total' => 28000,
            'status' => $status,
            'ordered_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 5000,
            'quantity' => 2,
            'subtotal' => 10000,
        ]);

        return $order;
    }
}
