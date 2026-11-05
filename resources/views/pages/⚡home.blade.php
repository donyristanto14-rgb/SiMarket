<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()
            ->withCount(['products' => fn ($query) => $query->where('status', 'active')])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function featuredProducts(): Collection
    {
        return Product::query()
            ->with(['category', 'seller', 'images'])
            ->where('status', 'active')
            ->where('is_featured', true)
            ->latest()
            ->take(4)
            ->get();
    }

    #[Computed]
    public function latestProducts(): Collection
    {
        return Product::query()
            ->with(['category', 'seller', 'images'])
            ->where('status', 'active')
            ->latest()
            ->take(4)
            ->get();
    }
};
?>

<div id="top" class="pb-24 md:pb-0">
    <x-site-header />

    <main>
        <section class="overflow-hidden bg-white">
            <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-12 sm:px-6 sm:py-16 md:grid-cols-[1.05fr_0.95fr] md:gap-8 md:py-20 lg:px-8 lg:py-24">
                <div class="max-w-2xl">
                    <p class="mb-5 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold tracking-wide text-blue-800">
                        <span class="size-1.5 rounded-full bg-accent-500"></span>
                        MARKETPLACE KOMUNITAS SEKOLAH
                    </p>
                    <h1 class="text-4xl font-bold leading-[1.12] tracking-tight text-navy-950 sm:text-5xl lg:text-[3.5rem]">
                        Barang sekolah yang tepat, dari teman satu komunitas.
                    </h1>
                    <p class="mt-5 max-w-xl text-base leading-7 text-slate-600 sm:text-lg">
                        Temukan perlengkapan baru dan preloved yang masih layak pakai. Jual, beli, dan atur pesanan dengan lebih mudah.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="#kategori" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-navy-950 px-5 text-sm font-semibold text-white transition hover:bg-blue-950">
                            Lihat kategori
                        </a>
                        <a href="#cara-kerja" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-line bg-white px-5 text-sm font-semibold text-navy-950 transition hover:bg-slate-50">
                            Cara kerja SiMarket
                        </a>
                    </div>
                    <p class="mt-5 text-sm text-slate-500">Find It. Trade It. Own It.</p>
                </div>

                <div class="relative mx-auto flex aspect-[1.2/1] w-full max-w-[500px] items-center justify-center rounded-3xl bg-canvas p-6 sm:p-10" aria-hidden="true">
                    <div class="absolute left-7 top-7 size-14 rounded-2xl border border-line bg-white sm:left-10 sm:top-10"></div>
                    <div class="absolute bottom-8 right-8 size-20 rounded-full border border-blue-100 bg-blue-50 sm:bottom-12 sm:right-12"></div>
                    <div class="relative w-full max-w-[290px] rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex items-start justify-between">
                            <span class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-navy-950">
                                <svg viewBox="0 0 24 24" fill="none" class="size-6" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path d="M5 4.75h9.5A3.5 3.5 0 0 1 18 8.25v11H8.5A3.5 3.5 0 0 1 5 15.75v-11Z" stroke-linejoin="round"/>
                                    <path d="M5 15.75a3.5 3.5 0 0 1 3.5-3.5H18M9 8h5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">Komunitas sekolah</span>
                        </div>
                        <div class="mt-7 space-y-3">
                            <div class="h-2.5 w-3/4 rounded-full bg-slate-200"></div>
                            <div class="h-2.5 w-1/2 rounded-full bg-slate-100"></div>
                        </div>
                        <div class="mt-6 grid grid-cols-3 gap-2">
                            <div class="h-14 rounded-lg bg-blue-50"></div>
                            <div class="h-14 rounded-lg bg-slate-100"></div>
                            <div class="h-14 rounded-lg bg-blue-50"></div>
                        </div>
                        <div class="mt-5 flex items-center gap-2 border-t border-line pt-4">
                            <span class="size-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-medium text-slate-600">Jual beli yang lebih dekat</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative z-10 mx-auto -mt-2 max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Cari produk">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-3 shadow-sm sm:flex-row sm:items-center sm:p-2 sm:pl-5">
                <label for="product-search" class="sr-only">Cari produk</label>
                <svg viewBox="0 0 24 24" fill="none" class="hidden size-5 shrink-0 text-slate-400 sm:block" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="10.8" cy="10.8" r="6.8"/>
                    <path d="m16 16 4.5 4.5" stroke-linecap="round"/>
                </svg>
                <input
                    id="product-search"
                    name="q"
                    type="search"
                    placeholder="Cari buku, alat tulis, dan lainnya"
                    class="min-h-11 min-w-0 flex-1 border-0 bg-transparent px-2 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:ring-0"
                >
                <button type="submit" class="min-h-11 rounded-xl bg-accent-500 px-6 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Cari
                </button>
            </form>
        </section>

        <section id="kategori" class="mx-auto max-w-7xl scroll-mt-8 px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-accent-500">Jelajahi</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight text-navy-950 sm:text-3xl">Kategori pilihan</h2>
                </div>
                <p class="max-w-sm text-sm leading-6 text-slate-500">Temukan kebutuhan sekolah dan barang menarik dari sesama siswa.</p>
            </div>
            <div class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                @forelse ($this->categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="flex min-h-28 flex-col justify-between rounded-2xl border border-line bg-white p-4 transition hover:border-blue-200 sm:min-h-32 sm:p-5">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-navy-950">{{ mb_substr($category->name, 0, 1) }}</span>
                        <span class="mt-4 text-sm font-semibold text-slate-800">{{ $category->name }}</span>
                        <span class="mt-1 text-xs text-slate-500">{{ $category->products_count }} produk</span>
                    </a>
                @empty
                    <p class="col-span-full rounded-2xl border border-dashed border-line bg-white px-5 py-8 text-center text-sm text-slate-500">Kategori akan muncul setelah data marketplace disiapkan.</p>
                @endforelse
            </div>
        </section>

        <section id="produk-pilihan" class="scroll-mt-8 border-y border-line bg-white">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-accent-500">Pilihan komunitas</p>
                        <h2 class="mt-1 text-2xl font-bold tracking-tight text-navy-950 sm:text-3xl">Produk pilihan</h2>
                    </div>
                    @if ($this->featuredProducts->isEmpty())
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">Belum ada pilihan</span>
                    @endif
                </div>
                @if ($this->featuredProducts->isEmpty())
                    <div class="mt-7 flex flex-col items-center rounded-2xl border border-dashed border-line bg-canvas px-5 py-12 text-center sm:py-14">
                        <h3 class="text-base font-semibold text-navy-950">Belum ada produk pilihan</h3>
                        <p class="mt-1 max-w-md text-sm leading-6 text-slate-500">Produk aktif yang dipilih admin akan muncul di sini.</p>
                    </div>
                @else
                    <div class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                        @foreach ($this->featuredProducts as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section id="produk-terbaru" class="mx-auto max-w-7xl scroll-mt-8 px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-accent-500">Baru ditambahkan</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight text-navy-950 sm:text-3xl">Produk terbaru</h2>
                </div>
            </div>
            @if ($this->latestProducts->isEmpty())
                <div class="mt-7 flex flex-col items-center rounded-2xl border border-dashed border-line bg-white px-5 py-12 text-center sm:py-14">
                    <h3 class="text-base font-semibold text-navy-950">Belum ada produk yang tayang</h3>
                    <p class="mt-1 max-w-md text-sm leading-6 text-slate-500">Listing baru akan muncul setelah disetujui admin.</p>
                </div>
            @else
                <div class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                    @foreach ($this->latestProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-7 text-center">
                    <a href="{{ route('products.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-line bg-white px-5 text-sm font-semibold text-navy-950 transition hover:bg-slate-50">Lihat semua produk</a>
                </div>
            @endif
        </section>

        <section id="cara-kerja" class="border-y border-line bg-white">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold text-accent-500">Mudah dan lebih aman</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight text-navy-950 sm:text-3xl">Jual beli dalam komunitasmu</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">SiMarket membantu mengatur pesanan. Pembayaran dilakukan di luar website, dan serah-terima sebaiknya dilakukan di lokasi sekolah yang disetujui serta diawasi.</p>
                </div>
                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    @foreach ([
                        ['number' => '01', 'title' => 'Temukan', 'description' => 'Jelajahi barang baru dan preloved yang sesuai kebutuhanmu.'],
                        ['number' => '02', 'title' => 'Pesan', 'description' => 'Ajukan pesanan melalui SiMarket tanpa pembayaran online.'],
                        ['number' => '03', 'title' => 'Atur serah-terima', 'description' => 'Sepakati waktu dan lokasi sekolah yang aman bersama penjual.'],
                    ] as $step)
                        <div class="rounded-2xl border border-line bg-canvas p-5 sm:p-6">
                            <span class="text-xs font-bold tracking-wider text-accent-500">{{ $step['number'] }}</span>
                            <h3 class="mt-3 font-semibold text-navy-950">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $step['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-navy-950 text-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 py-8 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
            <div>
                <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight">SiMarket</a>
                <p class="mt-1 text-sm text-blue-100/75">Find It. Trade It. Own It.</p>
            </div>
            <p class="max-w-xl text-xs leading-5 text-blue-100/75">
                Pembayaran tidak diproses di SiMarket. Jaga privasi dan gunakan lokasi serah-terima sekolah yang disetujui; jangan membagikan alamat rumah atau kontak pribadi secara publik.
            </p>
        </div>
    </footer>

    <nav class="fixed inset-x-0 bottom-0 z-20 border-t border-line bg-white/95 px-3 pb-[max(env(safe-area-inset-bottom),0.5rem)] pt-2 backdrop-blur md:hidden" aria-label="Navigasi bawah">
        <div class="mx-auto grid max-w-lg grid-cols-4">
            <a href="#top" class="flex min-h-12 flex-col items-center justify-center gap-1 text-[11px] font-medium text-navy-950">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path d="m3.75 10 8.25-6.5 8.25 6.5v9a1.25 1.25 0 0 1-1.25 1.25h-14A1.25 1.25 0 0 1 3.75 19v-9Z" stroke-linejoin="round"/>
                    <path d="M9 20.25v-6h6v6" stroke-linejoin="round"/>
                </svg>
                Beranda
            </a>
            <a href="#kategori" class="flex min-h-12 flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <rect x="4" y="4" width="6.5" height="6.5" rx="1.2"/>
                    <rect x="13.5" y="4" width="6.5" height="6.5" rx="1.2"/>
                    <rect x="4" y="13.5" width="6.5" height="6.5" rx="1.2"/>
                    <rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.2"/>
                </svg>
                Kategori
            </a>
            <a href="#produk-terbaru" class="flex min-h-12 flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path d="M5 7.5h14v12H5zM8 7.5V5h8v2.5M8.5 12h7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Produk
            </a>
            <a href="#cara-kerja" class="flex min-h-12 flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <circle cx="12" cy="12" r="8.5"/>
                    <path d="M9.8 9.2a2.3 2.3 0 1 1 3.9 1.6c-1.1 1-1.7 1.3-1.7 2.7M12 16.8h.01" stroke-linecap="round"/>
                </svg>
                Info
            </a>
        </div>
    </nav>
</div>