<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Database\Seeders\DemoMarketplaceSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketplaceSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_models_persist_their_ownership_relationships(): void
    {
        $seller = User::factory()->create();
        $category = Category::create([
            'name' => 'Buku & Alat Tulis',
            'slug' => 'buku-alat-tulis',
        ]);
        $product = $seller->products()->create([
            'category_id' => $category->id,
            'name' => 'Buku catatan',
            'slug' => 'buku-catatan',
            'description' => 'Buku catatan demonstrasi.',
            'price' => 12000,
            'condition' => 'new',
            'stock' => 2,
        ]);
        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/demo-buku.jpg',
        ]);
        $cart = Cart::create(['user_id' => $seller->id]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->assertSame(User::ROLE_STUDENT, $seller->fresh()->role);
        $this->assertSame($seller->id, $product->seller->id);
        $this->assertSame($category->id, $product->category->id);
        $this->assertSame($product->id, $image->product->id);
        $this->assertSame($product->id, $cartItem->product->id);
        $this->assertSame($cart->id, $cartItem->cart->id);
        $this->assertTrue($seller->products->contains('id', $product->id));
    }

    public function test_a_student_can_have_only_one_cart(): void
    {
        $user = User::factory()->create();
        Cart::create(['user_id' => $user->id]);

        $this->expectException(QueryException::class);

        Cart::create(['user_id' => $user->id]);
    }

    public function test_order_items_keep_product_snapshots_after_product_removal(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);
        $product = $seller->products()->create([
            'category_id' => $category->id,
            'name' => 'Kalkulator ilmiah',
            'slug' => 'kalkulator-ilmiah',
            'description' => 'Kalkulator demonstrasi.',
            'price' => 85000,
            'condition' => 'preloved',
            'stock' => 1,
        ]);
        $product->forceFill(['status' => 'active'])->save();
        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'order_code' => 'SM-DEMO-0001',
            'subtotal' => 85000,
            'total' => 85000,
        ]);
        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        $this->assertSame($buyer->id, $order->buyer->id);
        $this->assertSame($seller->id, $order->seller->id);
        $this->assertSame($order->id, $item->order->id);
        $this->assertSame('pending', $order->status);

        $product->delete();
        $this->assertSame($product->id, $item->fresh()->product->id);

        $product->forceDelete();
        $item->refresh();

        $this->assertNull($item->product_id);
        $this->assertSame('Kalkulator ilmiah', $item->product_name);
        $this->assertNull($item->product);
    }

    public function test_demo_seeder_is_repeatable_and_uses_an_environment_password(): void
    {
        $password = Str::random(48);
        config(['simarket.demo_password' => $password]);

        $this->seed(DemoMarketplaceSeeder::class);
        $this->seed(DemoMarketplaceSeeder::class);

        $admin = User::query()->where('email', 'admin@simarket.test')->firstOrFail();

        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check($password, $admin->password));
        $this->assertSame(3, User::query()->count());
        $this->assertSame(4, Category::query()->count());
        $this->assertSame(5, Product::query()->count());
        $this->assertSame(5, Product::query()->where('status', 'active')->count());
    }
}
