@extends('layouts.app')

@section('content')
    <x-site-header />

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold text-accent-500">Marketplace siswa</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-navy-950 sm:text-4xl">Katalog produk</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">Jelajahi listing aktif dari komunitas sekolah. Kontak pribadi penjual tidak ditampilkan.</p>
        </div>

        <form method="GET" action="{{ route('products.index') }}" class="mt-7 grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.5fr)_1fr_1fr_1fr_auto] lg:items-end">
            <div class="sm:col-span-2 lg:col-span-1">
                <label for="q" class="mb-1.5 block text-xs font-semibold text-slate-600">Cari produk</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Nama atau deskripsi produk" class="min-h-11 w-full rounded-xl border border-line px-3 text-sm outline-none focus:border-accent-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div>
                <label for="category" class="mb-1.5 block text-xs font-semibold text-slate-600">Kategori</label>
                <select id="category" name="category" class="min-h-11 w-full rounded-xl border border-line bg-white px-3 text-sm outline-none focus:border-accent-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="condition" class="mb-1.5 block text-xs font-semibold text-slate-600">Kondisi</label>
                <select id="condition" name="condition" class="min-h-11 w-full rounded-xl border border-line bg-white px-3 text-sm outline-none focus:border-accent-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua kondisi</option>
                    <option value="new" @selected(($filters['condition'] ?? '') === 'new')>Baru</option>
                    <option value="preloved" @selected(($filters['condition'] ?? '') === 'preloved')>Preloved</option>
                </select>
            </div>
            <div>
                <label for="sort" class="mb-1.5 block text-xs font-semibold text-slate-600">Urutkan</label>
                <select id="sort" name="sort" class="min-h-11 w-full rounded-xl border border-line bg-white px-3 text-sm outline-none focus:border-accent-500 focus:ring-2 focus:ring-blue-100">
                    <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>Terbaru</option>
                    <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Harga terendah</option>
                    <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Harga tertinggi</option>
                </select>
            </div>
            <div class="flex gap-2 sm:col-span-2 lg:col-span-1">
                <button type="submit" class="min-h-11 flex-1 rounded-xl bg-navy-950 px-4 text-sm font-semibold text-white transition hover:bg-blue-950">Terapkan</button>
                <a href="{{ route('products.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-line px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
            </div>
        </form>

        <div class="mt-8 flex items-center justify-between gap-3">
            <p class="text-sm text-slate-600"><span class="font-semibold text-navy-950">{{ $products->total() }}</span> produk ditemukan</p>
            @auth
                @if (auth()->user()->role === \App\Models\User::ROLE_STUDENT)
                    <a href="{{ route('seller.products.create') }}" class="inline-flex min-h-10 items-center rounded-xl bg-blue-50 px-3 text-sm font-semibold text-blue-800 transition hover:bg-blue-100">Jual produk</a>
                @endif
            @else
                <a href="{{ route('register') }}" class="inline-flex min-h-10 items-center rounded-xl bg-blue-50 px-3 text-sm font-semibold text-blue-800 transition hover:bg-blue-100">Mulai berjualan</a>
            @endauth
        </div>

        @if ($products->isEmpty())
            <div class="mt-5 rounded-2xl border border-dashed border-line bg-white px-5 py-14 text-center">
                <h2 class="text-lg font-semibold text-navy-950">Produk belum ditemukan</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">Coba ubah kata kunci atau filter. Produk baru akan tampil setelah disetujui admin.</p>
            </div>
        @else
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </main>

    <x-site-footer />
@endsection
