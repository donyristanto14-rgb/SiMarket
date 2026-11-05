@extends('layouts.app')

@section('content')
    <main class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-2.5">
                <span class="flex size-10 items-center justify-center rounded-xl bg-navy-950 text-sm font-bold text-white">S</span>
                <span class="text-lg font-bold tracking-tight text-navy-950">SiMarket</span>
            </a>

            <section class="rounded-2xl border border-line bg-white p-6 shadow-sm sm:p-8">
                <p class="text-sm font-semibold text-accent-500">Bergabung dengan komunitas</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-navy-950">Buat akun siswa</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Cukup gunakan nama dan email. Jangan masukkan nomor identitas atau informasi sensitif.</p>

                <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            autocomplete="name"
                            maxlength="100"
                            required
                            autofocus
                            class="min-h-12 w-full rounded-xl border border-line bg-white px-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-accent-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Nama yang ingin ditampilkan"
                        >
                        @error('name')
                            <p class="mt-1.5 text-sm text-red-700" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            maxlength="255"
                            required
                            class="min-h-12 w-full rounded-xl border border-line bg-white px-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-accent-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="nama@email.com"
                        >
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-700" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Kata sandi</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            minlength="8"
                            required
                            class="min-h-12 w-full rounded-xl border border-line bg-white px-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-accent-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Minimal 8 karakter"
                        >
                        @error('password')
                            <p class="mt-1.5 text-sm text-red-700" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Konfirmasi kata sandi</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            minlength="8"
                            required
                            class="min-h-12 w-full rounded-xl border border-line bg-white px-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-accent-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Ulangi kata sandi"
                        >
                    </div>

                    <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-navy-950 px-5 text-sm font-semibold text-white transition hover:bg-blue-950">
                        Buat akun siswa
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-600">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:text-blue-800">Masuk</a>
                </p>
            </section>

            <p class="mt-5 text-center text-xs leading-5 text-slate-500">Pendaftaran demo terbuka dan belum memverifikasi keanggotaan sekolah.</p>
        </div>
    </main>
@endsection
