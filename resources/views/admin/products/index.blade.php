@extends('layouts.app')

@section('content')
    <x-site-header />

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <div>
            <p class="text-sm font-semibold text-accent-500">Admin SiMarket</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-navy-950">Moderasi produk</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Tinjau listing agar tetap aman, sesuai untuk sekolah, dan tidak memuat informasi kontak pribadi.</p>
        </div>

        @if (session('status'))
            <p class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">{{ session('status') }}</p>
        @endif

        <nav class="mt-6 flex gap-2 border-b border-line" aria-label="Filter status moderasi">
            <a href="{{ route('admin.products.index', ['status' => 'pending']) }}" @class([
                'border-b-2 px-3 py-3 text-sm font-semibold',
                'border-navy-950 text-navy-950' => $status === 'pending',
                'border-transparent text-slate-500 hover:text-navy-950' => $status !== 'pending',
            ])>Menunggu</a>
            <a href="{{ route('admin.products.index', ['status' => 'active']) }}" @class([
                'border-b-2 px-3 py-3 text-sm font-semibold',
                'border-navy-950 text-navy-950' => $status === 'active',
                'border-transparent text-slate-500 hover:text-navy-950' => $status !== 'active',
            ])>Tayang</a>
        </nav>

        @if ($products->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-line bg-white px-5 py-14 text-center">
                <h2 class="text-lg font-semibold text-navy-950">{{ $status === 'pending' ? 'Tidak ada listing menunggu' : 'Tidak ada listing aktif' }}</h2>
                <p class="mt-2 text-sm text-slate-500">Daftar ini akan terisi berdasarkan status produk yang sebenarnya.</p>
            </div>
        @else
            <div class="mt-6 grid gap-4">
                @foreach ($products as $product)
                    <article class="rounded-2xl border border-line bg-white p-4 sm:p-5">
                        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(260px,0.65fr)]">
                            <div class="flex gap-4">
                                <div class="size-20 shrink-0 overflow-hidden rounded-xl bg-canvas sm:size-24">
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
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-500">{{ $product->category->name }} · {{ $product->condition_label }}</p>
                                    <h2 class="mt-1 font-semibold text-navy-950">{{ $product->name }}</h2>
                                    <p class="mt-1 text-sm font-bold text-slate-800">Rp{{ number_format((float) $product->price, 0, ',', '.') }} · Stok {{ $product->stock }}</p>
                                    <p class="mt-2 text-xs text-slate-500">Penjual: {{ $product->seller->name }}</p>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $product->description }}</p>
                                </div>
                            </div>

                            @if ($status === 'pending')
                                <div class="flex flex-col gap-3 lg:border-l lg:border-line lg:pl-5">
                                    <form method="POST" action="{{ route('admin.products.approve', $product) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="min-h-11 w-full rounded-xl bg-emerald-700 px-4 text-sm font-semibold text-white transition hover:bg-emerald-800">Setujui listing</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.products.reject', $product) }}" class="space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <label for="reason-{{ $product->id }}" class="block text-xs font-semibold text-slate-600">Alasan penolakan</label>
                                        <textarea id="reason-{{ $product->id }}" name="reason" rows="3" maxlength="2000" required class="w-full rounded-xl border border-line px-3 py-2 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100" placeholder="Jelaskan perbaikan yang diperlukan"></textarea>
                                        <button type="submit" class="min-h-11 w-full rounded-xl border border-red-200 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-50">Tolak listing</button>
                                    </form>
                                </div>
                            @else
                                <div class="flex flex-col gap-3 lg:border-l lg:border-line lg:pl-5">
                                    <form method="POST" action="{{ route('admin.products.feature', $product) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="min-h-11 w-full rounded-xl border border-line px-4 text-sm font-semibold text-navy-950 transition hover:bg-slate-50">
                                            {{ $product->is_featured ? 'Hapus dari produk pilihan' : 'Jadikan produk pilihan' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.products.suspend', $product) }}" class="space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <label for="reason-{{ $product->id }}" class="block text-xs font-semibold text-slate-600">Alasan penangguhan</label>
                                        <textarea id="reason-{{ $product->id }}" name="reason" rows="3" maxlength="2000" required class="w-full rounded-xl border border-line px-3 py-2 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100" placeholder="Jelaskan alasan listing ditangguhkan"></textarea>
                                        <button type="submit" class="min-h-11 w-full rounded-xl border border-red-200 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-50">Tangguhkan listing</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </main>

    <x-site-footer />
@endsection
