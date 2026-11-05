@extends('layouts.app')

@section('content')
    <x-site-header />

    @php($editing = $product->exists)

    <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 sm:py-12">
        <a href="{{ route('seller.products.index') }}" class="inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-800">
            <span aria-hidden="true">&larr;</span> Kembali ke etalase
        </a>

        <section class="mt-4 rounded-2xl border border-line bg-white p-5 sm:p-8">
            <p class="text-sm font-semibold text-accent-500">Listing siswa</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-navy-950">{{ $editing ? 'Edit produk' : 'Tambah produk' }}</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Isi detail dengan jujur. Perubahan listing akan ditinjau admin sebelum ditayangkan.</p>

            @if ($editing && $product->rejection_reason)
                <p class="mt-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm leading-6 text-red-800"><span class="font-semibold">Catatan admin:</span> {{ $product->rejection_reason }}</p>
            @endif

            <form
                method="POST"
                action="{{ $editing ? route('seller.products.update', $product) : route('seller.products.store') }}"
                enctype="multipart/form-data"
                class="mt-7 space-y-5"
            >
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama produk</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" maxlength="255" required class="min-h-12 w-full rounded-xl border border-line px-3.5 text-sm outline-none focus:border-accent-500 focus:ring-2 focus:ring-blue-100">
                    @error('name')<p class="mt-1.5 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="category_id" class="mb-1.5 block text-sm font-medium text-slate-700">Kategori</label>
                        <select id="category_id" name="category_id" required class="min-h-12 w-full rounded-xl border border-line bg-white px-3.5 text-sm outline-none focus:border-accent-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1.5 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="condition" class="mb-1.5 block text-sm font-medium text-slate-700">Kondisi</label>
                        <select id="condition" name="condition" required class="min-h-12 w-full rounded-xl border border-line bg-white px-3.5 text-sm outline-none focus:border-accent-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih kondisi</option>
                            <option value="new" @selected(old('condition', $product->condition) === 'new')>Baru</option>
                            <option value="preloved" @selected(old('condition', $product->condition) === 'preloved')>Preloved</option>
                        </select>
                        @error('condition')<p class="mt-1.5 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="price" class="mb-1.5 block text-sm font-medium text-slate-700">Harga (Rp)</label>
                        <input id="price" name="price" type="number" min="0.01" step="0.01" value="{{ old('price', $product->price) }}" required class="min-h-12 w-full rounded-xl border border-line px-3.5 text-sm outline-none focus:border-accent-500 focus:ring-2 focus:ring-blue-100">
                        @error('price')<p class="mt-1.5 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="stock" class="mb-1.5 block text-sm font-medium text-slate-700">Stok</label>
                        <input id="stock" name="stock" type="number" min="1" max="1000000" step="1" value="{{ old('stock', $product->stock ?: 1) }}" required class="min-h-12 w-full rounded-xl border border-line px-3.5 text-sm outline-none focus:border-accent-500 focus:ring-2 focus:ring-blue-100">
                        @error('stock')<p class="mt-1.5 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea id="description" name="description" rows="5" maxlength="5000" required class="w-full rounded-xl border border-line px-3.5 py-3 text-sm leading-6 outline-none focus:border-accent-500 focus:ring-2 focus:ring-blue-100">{{ old('description', $product->description) }}</textarea>
                    @error('description')<p class="mt-1.5 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="images" class="mb-1.5 block text-sm font-medium text-slate-700">Foto produk</label>
                    <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple class="block min-h-12 w-full rounded-xl border border-line bg-white text-sm text-slate-600 file:mr-4 file:min-h-12 file:border-0 file:bg-slate-50 file:px-4 file:text-sm file:font-semibold file:text-navy-950">
                    <p class="mt-1.5 text-xs leading-5 text-slate-500">Maksimal 5 gambar per unggahan. JPG, PNG, atau WebP, maksimal 5 MB per file.</p>
                    @error('images')<p class="mt-1.5 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                    @foreach ($errors->get('images.*') as $messages)
                        @foreach ($messages as $message)
                            <p class="mt-1.5 text-sm text-red-700" role="alert">{{ $message }}</p>
                        @endforeach
                    @endforeach
                    @if ($editing && $product->images->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-3">
                            @foreach ($product->images as $image)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->image_path) }}" alt="{{ $product->name }}" class="size-16 rounded-lg border border-line object-cover">
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-xl bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-900">
                    Pembayaran tidak diproses di website. Atur serah-terima di lokasi sekolah yang disetujui; jangan mencantumkan kontak pribadi atau alamat rumah.
                </div>

                <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-navy-950 px-5 text-sm font-semibold text-white transition hover:bg-blue-950">
                    {{ $editing ? 'Simpan perubahan untuk ditinjau' : 'Kirim listing untuk ditinjau' }}
                </button>
            </form>
        </section>
    </main>

    <x-site-footer />
@endsection
