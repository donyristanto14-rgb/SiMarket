<header class="border-b border-line bg-white">
    <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:min-h-[76px] lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="SiMarket, beranda">
            <span class="flex size-9 items-center justify-center rounded-xl bg-navy-950 text-sm font-bold text-white">S</span>
            <span class="text-lg font-bold tracking-tight text-navy-950">SiMarket</span>
        </a>

        <nav class="hidden items-center gap-7 text-sm font-medium text-slate-600 md:flex" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="transition hover:text-navy-950">Beranda</a>
            <a href="{{ route('products.index') }}" class="transition hover:text-navy-950">Katalog</a>
            <a href="{{ route('home') }}#cara-kerja" class="transition hover:text-navy-950">Cara kerja</a>
        </nav>

        <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
            @auth
                @if (auth()->user()->role === \App\Models\User::ROLE_STUDENT)
                    <a href="{{ route('seller.products.index') }}" class="hidden min-h-10 items-center rounded-lg px-2.5 text-sm font-semibold text-navy-950 transition hover:bg-slate-50 sm:inline-flex">
                        Etalase saya
                    </a>
                @elseif (auth()->user()->isAdmin())
                    <a href="{{ route('admin.products.index') }}" class="hidden min-h-10 items-center rounded-lg px-2.5 text-sm font-semibold text-navy-950 transition hover:bg-slate-50 sm:inline-flex">
                        Moderasi
                    </a>
                @endif
                <span class="hidden max-w-36 truncate text-sm font-medium text-slate-600 lg:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex min-h-10 items-center rounded-lg border border-line px-2.5 text-sm font-semibold text-navy-950 transition hover:bg-slate-50 sm:px-3">
                        Keluar
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="inline-flex min-h-10 items-center rounded-lg px-2.5 text-sm font-semibold text-navy-950 transition hover:bg-slate-50">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="inline-flex min-h-10 items-center rounded-lg bg-navy-950 px-3 text-sm font-semibold text-white transition hover:bg-blue-950">
                    Daftar
                </a>
            @endauth
        </div>
    </div>
</header>
