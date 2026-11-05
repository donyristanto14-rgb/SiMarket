<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoMarketplaceSeeder extends Seeder
{
    /**
     * Seed demonstration-only marketplace accounts and listings.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demonstration marketplace data can only be seeded locally or in tests.');
        }

        $password = config('simarket.demo_password');

        if (! is_string($password) || trim($password) === '') {
            throw new RuntimeException('Set SIMARKET_DEMO_PASSWORD before seeding demonstration accounts.');
        }

        $users = [];

        foreach ([
            'admin' => ['name' => 'Demo Admin SiMarket', 'email' => 'admin@simarket.test', 'role' => User::ROLE_ADMIN],
            'nabila' => ['name' => 'Demo Nabila Putri', 'email' => 'nabila@simarket.test', 'role' => User::ROLE_STUDENT],
            'rafi' => ['name' => 'Demo Rafi Pratama', 'email' => 'rafi@simarket.test', 'role' => User::ROLE_STUDENT],
        ] as $key => $details) {
            $user = User::query()->firstOrNew(['email' => $details['email']]);
            $user->forceFill([
                ...$details,
                'password' => $password,
            ])->save();
            $users[$key] = $user;
        }

        $categories = [];

        foreach ([
            'buku-alat-tulis' => ['name' => 'Buku & Alat Tulis', 'description' => 'Buku pelajaran dan perlengkapan belajar.'],
            'fashion' => ['name' => 'Fashion', 'description' => 'Pakaian dan perlengkapan sekolah yang layak pakai.'],
            'aksesoris' => ['name' => 'Aksesoris', 'description' => 'Aksesoris sederhana untuk kegiatan sehari-hari.'],
            'elektronik' => ['name' => 'Elektronik', 'description' => 'Perangkat elektronik yang sesuai untuk kegiatan belajar.'],
        ] as $slug => $details) {
            $categories[$slug] = Category::query()->updateOrCreate(['slug' => $slug], $details);
        }

        foreach ([
            [
                'seller' => 'nabila',
                'category' => 'buku-alat-tulis',
                'name' => 'Buku latihan matematika kelas XI',
                'slug' => 'demo-buku-latihan-matematika-xi',
                'description' => 'Demo: Buku latihan dengan sampul rapi dan beberapa halaman sudah terisi.',
                'price' => 18000,
                'condition' => 'preloved',
                'stock' => 1,
                'is_featured' => true,
            ],
            [
                'seller' => 'rafi',
                'category' => 'buku-alat-tulis',
                'name' => 'Set alat tulis lengkap',
                'slug' => 'demo-set-alat-tulis-lengkap',
                'description' => 'Demo: Set alat tulis baru untuk membantu kegiatan belajar.',
                'price' => 25000,
                'condition' => 'new',
                'stock' => 4,
                'is_featured' => true,
            ],
            [
                'seller' => 'nabila',
                'category' => 'fashion',
                'name' => 'Jaket olahraga sekolah',
                'slug' => 'demo-jaket-olahraga-sekolah',
                'description' => 'Demo: Jaket olahraga preloved yang masih nyaman dan bersih.',
                'price' => 55000,
                'condition' => 'preloved',
                'stock' => 1,
            ],
            [
                'seller' => 'rafi',
                'category' => 'aksesoris',
                'name' => 'Tempat pensil minimalis',
                'slug' => 'demo-tempat-pensil-minimalis',
                'description' => 'Demo: Tempat pensil baru dengan ruang untuk perlengkapan dasar.',
                'price' => 16000,
                'condition' => 'new',
                'stock' => 3,
            ],
            [
                'seller' => 'nabila',
                'category' => 'elektronik',
                'name' => 'Kalkulator ilmiah',
                'slug' => 'demo-kalkulator-ilmiah',
                'description' => 'Demo: Kalkulator ilmiah preloved, tombol berfungsi dengan baik.',
                'price' => 85000,
                'condition' => 'preloved',
                'stock' => 1,
            ],
        ] as $details) {
            $product = Product::withTrashed()->firstOrNew(['slug' => $details['slug']]);
            $product->forceFill([
                'user_id' => $users[$details['seller']]->id,
                'category_id' => $categories[$details['category']]->id,
                'name' => $details['name'],
                'description' => $details['description'],
                'price' => $details['price'],
                'condition' => $details['condition'],
                'stock' => $details['stock'],
                'status' => 'active',
                'is_featured' => $details['is_featured'] ?? false,
                'rejection_reason' => null,
                'deleted_at' => null,
            ])->save();
        }
    }
}
