@extends('layouts.app')

@section('content')
    <x-site-header />

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-accent-500">Penjual</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-navy-950">Etalase saya</h1>
                <p class="mt-2 text-sm text-slate-600">Kelola listing dan pantau status moderasinya.</p>
            </div>
            <a href="{{ route('seller.products.create') }}" class="inline-flex min-h-11 items-center rounded-xl bg-navy-950 px-4 text-sm font-semibold text-white transition hover:bg-blue-950">Tambah produk</a>
        </div>

        @if (session('status'))
            <p class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">{{ session('status') }}</p>
        @endif

        @if ($products->isEmpty())
            <div class="mt-7 rounded-2xl border border-dashed border-line bg-white px-5 py-14 text-center">
                <h2 class="text-lg font-semibold text-navy-950">Etalasemu masih kosong</h2>
                <p class="mt-2 text-sm text-slate-500">Buat listing pertama. Produk akan ditinjau admin sebelum tampil di katalog.</p>
            </div>
        @else
            <div class="mt-7 grid gap-4">
                @foreach ($products as $product)
                    <article class="rounded-2xl border border-line bg-white p-4 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                            <div class="size-24 shrink-0 overflow-hidden rounded-xl bg-canvas">
                                @if ($product->images->isNotEmpty())
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->images->first()->image_path) }}" alt="{{ $product->name }}" class="size-full object-cover">
                                @else
                                    <div class="flex size-full items-center justify-center text-slate-300" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" class="size-8" stroke="currentColor" stroke-width="1.4">
                                            <path d="M5 7.5h14v12H5zM8 7.5V5h8v2.5M8.5 12h7" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-amber-50 text-amber-800' => $product->status === 'pending',
                                        'bg-emerald-50 text-emerald-800' => $product->status === 'active',
                                        'bg-red-50 text-red-800' => $product->status === 'rejected',
                                        'bg-slate-100 text-slate-700' => $product->status === 'archived',
                                    ])>{{ $product->status_label }}</span>
                                    <span class="text-xs text-slate-500">{{ $product->category->name }} · {{ $product->condition_label }}</span>
                                </div>
                                <h2 class="mt-2 text-base font-semibold text-navy-950">{{ $product->name }}</h2>
                                <p class="mt-1 text-sm font-bold text-slate-800">Rp{{ number_format((float) $product->price, 0, ',', '.') }} · Stok {{ $product->stock }}</p>
                                @if ($product->status === 'rejected' && $product->rejection_reason)
                                    <p class="mt-3 rounded-xl bg-red-50 px-3 py-2 text-sm leading-6 text-red-800"><span class="font-semibold">Catatan admin:</span> {{ $product->rejection_reason }}</p>
                                @endif
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($product->status !== 'archived')
                                        <a href="{{ route('seller.products.edit', $product) }}" class="inline-flex min-h-10 items-center rounded-lg border border-line px-3 text-sm font-semibold text-navy-950 hover:bg-slate-50">Edit listing</a>
                                        @if ($product->status === 'active')
                                            <a href="{{ route('products.show', $product) }}" class="inline-flex min-h-10 items-center rounded-lg border border-line px-3 text-sm font-semibold text-navy-950 hover:bg-slate-50">Lihat listing</a>
                                        @endif
                                        <form method="POST" action="{{ route('seller.products.archive', $product) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-slate-600 hover:bg-slate-100">Arsipkan</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </main>

    <x-site-footer />
@endsection
