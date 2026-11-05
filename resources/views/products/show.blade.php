@extends('layouts.app')

@section('content')
    <x-site-header />

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <a href="{{ route('products.index') }}" class="inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-800">
            <span aria-hidden="true">&larr;</span> Kembali ke katalog
        </a>

        <div class="mt-5 grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:gap-12">
            <section aria-label="Foto produk">
                @if ($product->images->isNotEmpty())
                    <div class="overflow-hidden rounded-2xl border border-line bg-white">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->images->first()->image_path) }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                    </div>
                    @if ($product->images->count() > 1)
                        <div class="mt-3 grid grid-cols-4 gap-3">
                            @foreach ($product->images->skip(1) as $image)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->image_path) }}" alt="{{ $product->name }}" class="aspect-square w-full rounded-xl border border-line object-cover">
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="flex aspect-square items-center justify-center rounded-2xl border border-line bg-white text-slate-300">
                        <svg viewBox="0 0 24 24" fill="none" class="size-16" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                            <path d="M5 7.5h14v12H5zM8 7.5V5h8v2.5M8.5 12h7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                @endif
            </section>

            <section>
                <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">{{ $product->category->name }}</a>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800">{{ $product->condition_label }}</span>
                    @if ($product->is_featured)
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">Produk pilihan</span>
                    @endif
                </div>
                <h1 class="mt-4 text-3xl font-bold leading-tight tracking-tight text-navy-950 sm:text-4xl">{{ $product->name }}</h1>
                <p class="mt-4 text-2xl font-bold text-navy-950">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</p>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-line bg-white p-4">
                        <p class="text-xs font-medium text-slate-500">Ketersediaan</p>
                        <p class="mt-1 text-sm font-semibold {{ $product->stock > 0 ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ $product->stock > 0 ? 'Tersedia' : 'Stok habis' }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-line bg-white p-4">
                        <p class="text-xs font-medium text-slate-500">Stok</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $product->stock }} unit</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-line pt-6">
                    <h2 class="text-sm font-semibold text-navy-950">Deskripsi</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $product->description }}</p>
                </div>

                <div class="mt-6 rounded-2xl border border-line bg-white p-4 sm:p-5">
                    <p class="text-xs font-medium text-slate-500">Dijual oleh</p>
                    <p class="mt-1 font-semibold text-navy-950">{{ $product->seller->name }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Atur serah-terima di lokasi sekolah yang disetujui. Kontak pribadi penjual tidak ditampilkan.</p>
                </div>

                @auth
                    @if (auth()->id() === $product->user_id && $product->status !== 'archived')
                        <a href="{{ route('seller.products.edit', $product) }}" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl border border-line bg-white px-4 text-sm font-semibold text-navy-950 hover:bg-slate-50">Kelola listing</a>
                    @endif
                @endauth
            </section>
        </div>
    </main>

    <x-site-footer />
@endsection
