@props(['product'])

<a href="{{ route('products.show', $product) }}" class="group overflow-hidden rounded-2xl border border-line bg-white transition hover:border-blue-200 hover:shadow-sm">
    <div class="relative aspect-square overflow-hidden bg-canvas">
        @if ($product->images->isNotEmpty())
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->images->first()->image_path) }}"
                alt="{{ $product->name }}"
                loading="lazy"
                class="size-full object-cover transition duration-300 group-hover:scale-[1.02]"
            >
        @else
            <div class="flex size-full items-center justify-center text-slate-300" aria-label="Gambar belum tersedia">
                <svg viewBox="0 0 24 24" fill="none" class="size-12" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                    <path d="M5 7.5h14v12H5zM8 7.5V5h8v2.5M8.5 12h7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        @endif
        <span class="absolute left-2.5 top-2.5 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm">
            {{ $product->condition_label }}
        </span>
    </div>
    <div class="p-3.5 sm:p-4">
        <p class="truncate text-xs text-slate-500">{{ $product->category->name }}</p>
        <h3 class="mt-1 truncate text-sm font-semibold text-navy-950">{{ $product->name }}</h3>
        <p class="mt-2 text-base font-bold text-navy-950">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</p>
        <p class="mt-1 truncate text-xs text-slate-500">{{ $product->seller->name }}</p>
    </div>
</a>
