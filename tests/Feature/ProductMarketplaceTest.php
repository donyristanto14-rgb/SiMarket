<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Buku & Alat Tulis',
            'slug' => 'buku-alat-tulis',
        ]);
    }

    public function test_catalog_filters_searches_and_sorts_active_products(): void
    {
        $seller = User::factory()->create(['name' => 'Demo Penjual']);
        $book = $this->makeProduct($seller, [
            'name' => 'Buku catatan preloved',
            'description' => 'Buku tulis yang masih layak.',
            'price' => 15000,
            'condition' => 'preloved',
        ]);
        $pencil = $this->makeProduct($seller, [
            'name' => 'Pensil baru',
            'description' => 'Pensil untuk sekolah.',
            'price' => 5000,
            'condition' => 'new',
        ]);
        $pending = $this->makeProduct($seller, [
            'name' => 'Buku belum disetujui',
            'status' => 'pending',
        ]);

        $filtered = $this->get(route('products.index', [
            'q' => 'buku',
            'category' => $this->category->slug,
            'condition' => 'preloved',
            'sort' => 'price_asc',
        ]));

        $filtered->assertOk()
            ->assertSee($book->name)
            ->assertDontSee($pencil->name)
            ->assertDontSee($pending->name);

        $sorted = $this->get(route('products.index', ['sort' => 'price_asc']));
        $sorted->assertSeeInOrder([$pencil->name, $book->name]);
    }

    public function test_catalog_searches_product_descriptions(): void
    {
        $seller = User::factory()->create();
        $match = $this->makeProduct($seller, [
            'name' => 'Buku pelajaran',
            'description' => 'Sampul biru unik untuk pencarian.',
        ]);
        $this->makeProduct($seller, [
            'name' => 'Tempat pensil',
            'description' => 'Tempat menyimpan alat tulis.',
        ]);

        $this->get(route('products.index', ['q' => 'Sampul biru unik']))
            ->assertOk()
            ->assertSee($match->name)
            ->assertDontSee('Tempat pensil');
    }

    public function test_catalog_supports_all_sort_orders_and_paginates_results(): void
    {
        $seller = User::factory()->create();
        $oldest = $this->makeProduct($seller, ['name' => 'Produk tertua', 'price' => 10000]);
        $middle = $this->makeProduct($seller, ['name' => 'Produk tengah', 'price' => 5000]);
        $newest = $this->makeProduct($seller, ['name' => 'Produk terbaru', 'price' => 15000]);

        $oldest->forceFill(['created_at' => now()->subDays(3)])->save();
        $middle->forceFill(['created_at' => now()->subDays(2)])->save();
        $newest->forceFill(['created_at' => now()->subDay()])->save();

        $this->get(route('products.index', ['sort' => 'latest']))
            ->assertOk()
            ->assertSeeInOrder([$newest->name, $middle->name, $oldest->name]);

        $this->get(route('products.index', ['sort' => 'price_desc']))
            ->assertOk()
            ->assertSeeInOrder([$newest->name, $oldest->name, $middle->name]);

        $this->get(route('products.index', ['sort' => 'price_asc']))
            ->assertOk()
            ->assertSeeInOrder([$middle->name, $oldest->name, $newest->name]);

        for ($index = 1; $index <= 10; $index++) {
            $this->makeProduct($seller, ['name' => "Produk halaman {$index}"]);
        }

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSeeText('13 produk ditemukan')
            ->assertSee('page=2');

        $this->get(route('products.index', ['page' => 2]))
            ->assertOk()
            ->assertSee($oldest->name)
            ->assertDontSee($newest->name);
    }

    public function test_only_active_products_can_be_viewed_publicly(): void
    {
        $seller = User::factory()->create();
        $pending = $this->makeProduct($seller, ['name' => 'Listing menunggu', 'status' => 'pending']);
        $active = $this->makeProduct($seller, ['name' => 'Listing tayang']);

        $this->get(route('products.show', $pending))->assertNotFound();
        $this->get(route('products.show', $active))
            ->assertOk()
            ->assertSee($seller->name)
            ->assertDontSee($seller->email);
    }

    public function test_student_can_create_a_pending_listing_without_assigning_privileged_fields(): void
    {
        Storage::fake('public');

        $student = User::factory()->create();
        $otherStudent = User::factory()->create();

        $this->actingAs($student)
            ->post(route('seller.products.store'), [
                'name' => 'Buku geografi',
                'category_id' => $this->category->id,
                'description' => 'Buku dalam kondisi baik.',
                'price' => 12000,
                'condition' => 'preloved',
                'stock' => 2,
                'user_id' => $otherStudent->id,
                'status' => 'active',
                'is_featured' => true,
                'rejection_reason' => 'Tidak sesuai',
                'images' => [
                    UploadedFile::fake()->createWithContent(
                        'cover.png',
                        file_get_contents(base_path('vendor/livewire/livewire/src/Features/SupportFileUploads/browser_test_image.png'))
                    ),
                ],
            ])
            ->assertRedirect(route('seller.products.index'));

        $product = Product::query()->where('name', 'Buku geografi')->firstOrFail();

        $this->assertSame($student->id, $product->user_id);
        $this->assertSame('pending', $product->status);
        $this->assertFalse($product->is_featured);
        $this->assertNull($product->rejection_reason);
        $this->assertCount(1, $product->images);
        Storage::disk('public')->assertExists($product->images->first()->image_path);
    }

    public function test_product_images_are_validated(): void
    {
        $student = User::factory()->create();

        $this->from(route('seller.products.create'))
            ->actingAs($student)
            ->post(route('seller.products.store'), [
                'name' => 'Buku catatan',
                'category_id' => $this->category->id,
                'description' => 'Deskripsi singkat.',
                'price' => 10000,
                'condition' => 'new',
                'stock' => 1,
                'images' => [UploadedFile::fake()->create('bukan-gambar.txt', 10, 'text/plain')],
            ])
            ->assertRedirect(route('seller.products.create'))
            ->assertSessionHasErrors('images.0');
    }

    public function test_sellers_can_edit_and_archive_only_their_own_listings(): void
    {
        $owner = User::factory()->create();
        $otherStudent = User::factory()->create();
        $product = $this->makeProduct($owner, [
            'name' => 'Buku ekonomi',
            'status' => 'active',
            'rejection_reason' => null,
        ]);

        $this->actingAs($otherStudent)
            ->get(route('seller.products.edit', $product))
            ->assertForbidden();

        $this->actingAs($owner)
            ->put(route('seller.products.update', $product), [
                'name' => 'Buku ekonomi kelas XI',
                'category_id' => $this->category->id,
                'description' => 'Edisi terbaru, masih layak pakai.',
                'price' => 20000,
                'condition' => 'preloved',
                'stock' => 1,
            ])
            ->assertRedirect(route('seller.products.index'));

        $product->refresh();
        $this->assertSame('pending', $product->status);
        $this->assertSame('Buku ekonomi kelas XI', $product->name);

        $this->actingAs($owner)
            ->patch(route('seller.products.archive', $product))
            ->assertRedirect(route('seller.products.index'));

        $this->assertSame('archived', $product->fresh()->status);
        $this->get(route('products.show', $product))->assertNotFound();
    }

    public function test_other_students_cannot_update_or_archive_a_listing(): void
    {
        $owner = User::factory()->create();
        $otherStudent = User::factory()->create();
        $product = $this->makeProduct($owner, ['name' => 'Listing milik siswa lain']);

        $this->actingAs($otherStudent)
            ->put(route('seller.products.update', $product), [
                'name' => 'Perubahan tidak sah',
                'category_id' => $this->category->id,
                'description' => 'Percobaan mengubah listing orang lain.',
                'price' => 99999,
                'condition' => 'new',
                'stock' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($otherStudent)
            ->patch(route('seller.products.archive', $product))
            ->assertForbidden();

        $product->refresh();
        $this->assertSame('Listing milik siswa lain', $product->name);
        $this->assertSame('active', $product->status);
    }

    public function test_admin_can_moderate_feature_and_suspend_products(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
        $seller = User::factory()->create();
        $product = $this->makeProduct($seller, ['name' => 'Kalkulator ilmiah', 'status' => 'pending']);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee($product->name);

        $this->actingAs($admin)
            ->patch(route('admin.products.approve', $product))
            ->assertRedirect();
        $this->assertSame('active', $product->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.products.feature', $product))
            ->assertRedirect();
        $this->assertTrue($product->fresh()->is_featured);

        $this->actingAs($admin)
            ->patch(route('admin.products.suspend', $product), ['reason' => 'Listing perlu ditinjau kembali.'])
            ->assertRedirect();

        $product->refresh();
        $this->assertSame('rejected', $product->status);
        $this->assertFalse($product->is_featured);
        $this->assertSame('Listing perlu ditinjau kembali.', $product->rejection_reason);
        $this->get(route('products.show', $product))->assertNotFound();
    }

    public function test_admin_can_reject_a_pending_product_and_cannot_repeat_moderation(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
        $seller = User::factory()->create();
        $product = $this->makeProduct($seller, ['name' => 'Listing untuk ditolak', 'status' => 'pending']);

        $this->actingAs($admin)
            ->patch(route('admin.products.reject', $product), ['reason' => 'Barang ini tidak sesuai aturan sekolah.'])
            ->assertRedirect();

        $product->refresh();
        $this->assertSame('rejected', $product->status);
        $this->assertSame('Barang ini tidak sesuai aturan sekolah.', $product->rejection_reason);
        $this->assertNotFoundForPublic($product);

        $this->actingAs($admin)
            ->patch(route('admin.products.approve', $product))
            ->assertStatus(409);

        $this->actingAs($admin)
            ->patch(route('admin.products.reject', $product), ['reason' => 'Alasan kedua.'])
            ->assertStatus(409);
    }

    public function test_rejection_and_suspension_require_reasons_without_changing_status(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
        $seller = User::factory()->create();
        $pending = $this->makeProduct($seller, ['name' => 'Listing pending', 'status' => 'pending']);
        $active = $this->makeProduct($seller, ['name' => 'Listing aktif', 'status' => 'active']);

        $this->actingAs($admin)
            ->from(route('admin.products.index'))
            ->patch(route('admin.products.reject', $pending))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHasErrors('reason');

        $this->actingAs($admin)
            ->from(route('admin.products.index', ['status' => 'active']))
            ->patch(route('admin.products.suspend', $active))
            ->assertRedirect(route('admin.products.index', ['status' => 'active']))
            ->assertSessionHasErrors('reason');

        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertSame('active', $active->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.products.suspend', $pending), ['reason' => 'Status harus aktif untuk ditangguhkan.'])
            ->assertStatus(409);
    }

    public function test_homepage_renders_database_featured_products_without_empty_state_message(): void
    {
        $seller = User::factory()->create();
        $featured = $this->makeProduct($seller, ['name' => 'Pilihan komunitas']);
        $featured->forceFill(['is_featured' => true])->save();
        $latest = $this->makeProduct($seller, ['name' => 'Produk terbaru biasa']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['Produk pilihan', $featured->name, 'Produk terbaru', $latest->name])
            ->assertDontSee('Segera hadir')
            ->assertDontSee('Belum ada produk pilihan');
    }

    public function test_students_and_guests_cannot_access_product_moderation(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));

        $student = User::factory()->create();
        $this->actingAs($student)->get(route('admin.products.index'))->assertForbidden();
    }

    private function makeProduct(User $seller, array $attributes = []): Product
    {
        $name = $attributes['name'] ?? 'Produk demo';
        $status = $attributes['status'] ?? 'active';
        unset($attributes['name'], $attributes['status']);

        $product = $seller->products()->create([
            'category_id' => $this->category->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'description' => 'Deskripsi demonstrasi.',
            'price' => 10000,
            'condition' => 'new',
            'stock' => 1,
            ...$attributes,
        ]);

        $product->forceFill(['status' => $status])->save();

        return $product;
    }

    private function assertNotFoundForPublic(Product $product): void
    {
        $this->get(route('products.show', $product))->assertNotFound();
    }
}
