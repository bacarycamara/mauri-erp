<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ================= COMPANY CACHE ================= --}}
    @php
        $company = auth()->check() ? company() : null;
    @endphp

    {{-- ================= TITLE ================= --}}
    <title>
        {{ config('app.vendor.name') }} — {{ $company?->name ?? config('app.name') }}
    </title>

    {{-- ================= FAVICONS ================= --}}
    <link rel="icon" href="{{ asset(config('app.vendor.logo')) }}">
    <link rel="apple-touch-icon" href="{{ asset(config('app.vendor.logo')) }}">
    <meta name="theme-color" content="#312e81">

    {{-- ================= SEO ================= --}}
    <meta name="application-name" content="{{ config('app.vendor.name') }}">
    <meta name="author" content="Bacary Camara">
    <meta name="description" content="{{ config('app.vendor.name') }} - ERP Professionnel moderne">

    {{-- ================= FONTS ================= --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    {{-- ================= ASSETS ================= --}}
    @vite(['resources/css/app.css','resources/js/app.js'])

    @livewireStyles
    @stack('styles')

    <style>
        /* ── Sidebar overlay mobile ── */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 40;
            backdrop-filter: blur(2px);
        }
        #sidebar-overlay.active { display: block; }

        /* ── Sidebar mobile ── */
        #app-sidebar {
            transition: transform .28s cubic-bezier(.4,0,.2,1);
            flex-shrink: 0;
        }

        @media (max-width: 1023px) {
            #app-sidebar {
                position: fixed !important;
                top: 0; left: 0;
                height: 100dvh;
                z-index: 50;
                transform: translateX(-100%);
            }
            #app-sidebar.open {
                transform: translateX(0);
                box-shadow: 4px 0 30px rgba(0,0,0,.25);
            }
        }

        /* ── Tables scroll horizontal mobile ── */
        .table-responsive-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 12px;
        }

        /* ── Table mode cards mobile ── */
        @media (max-width: 767px) {
            .table-erp.table-cards thead { display: none; }
            .table-erp.table-cards tbody tr {
                display: block;
                border-bottom: 1px solid #e5e7eb;
                padding: 12px 16px;
            }
            .table-erp.table-cards tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border: none;
                padding: 5px 0;
                border-bottom: 1px solid #f8fafc;
                font-size: .82rem;
            }
            .table-erp.table-cards tbody td:last-child { border-bottom: none; }
            .table-erp.table-cards tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #6b7280;
                font-size: .72rem;
                text-transform: uppercase;
                letter-spacing: .4px;
                flex-shrink: 0;
                margin-right: 12px;
            }
            .table-erp.table-cards tbody td.no-label::before { display: none; }
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-100 text-gray-800">

{{-- ── Overlay mobile ── --}}
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<div class="flex h-screen overflow-hidden">

    {{-- ================= SIDEBAR ================= --}}
    <div id="app-sidebar">
        @include('layouts.navigation')
    </div>

    {{-- ================= MAIN ================= --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- ================= TOPBAR ================= --}}
        <header class="bg-white/80 backdrop-blur border-b border-gray-200 shadow-sm flex-shrink-0">
            <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4 gap-3">

                {{-- ── LEFT ── --}}
                <div class="flex items-center gap-3 md:gap-5 min-w-0 flex-1">

                    {{-- Burger (mobile/tablette uniquement) --}}
                    <button onclick="toggleSidebar()"
                        class="lg:hidden flex-shrink-0 p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition text-gray-600 touch-manipulation">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                        </svg>
                    </button>

                    {{-- Vendor logo --}}
                    <img src="{{ asset(config('app.vendor.logo')) }}"
                         class="h-8 md:h-9 object-contain flex-shrink-0">

                    {{-- Séparateur --}}
                    <div class="h-6 w-px bg-gray-200 flex-shrink-0 hidden sm:block"></div>

                    {{-- Company --}}
                    @if($company)
                    <div class="flex items-center gap-2 min-w-0 flex-shrink-0">
                        @if($company->logo_url)
                            <img src="{{ $company->logo_url }}"
                                 class="h-7 md:h-8 object-contain flex-shrink-0 hidden sm:block">
                        @endif
                        <div class="leading-tight min-w-0 hidden sm:block">
                            <div class="text-sm font-semibold text-gray-800 truncate max-w-[140px]">{{ $company->name }}</div>
                            <div class="text-xs text-gray-500">{{ $company->currency }}</div>
                        </div>
                        {{-- Mobile : juste le nom court --}}
                        <div class="sm:hidden leading-tight">
                            <div class="text-xs font-semibold text-gray-700 truncate max-w-[80px]">{{ $company->name }}</div>
                        </div>
                    </div>

                    {{-- Séparateur --}}
                    <div class="h-6 w-px bg-gray-200 flex-shrink-0 hidden md:block"></div>
                    @endif

                    {{-- Page title --}}
                    <div class="flex items-center gap-2 min-w-0">
                        <x-heroicon-o-squares-2x2 class="w-4 h-4 md:w-5 md:h-5 text-indigo-600 flex-shrink-0"/>
                        <h1 class="text-sm md:text-base font-semibold text-gray-800 tracking-wide truncate">
                            {{ $title ?? '' }}
                        </h1>
                    </div>
                </div>

                {{-- ── RIGHT — User ── --}}
                @auth
                <div x-data="{ open: false }" class="relative flex items-center gap-2 md:gap-3 flex-shrink-0">

                    {{-- Nom/email — desktop seulement --}}
                    <div class="hidden md:block text-right leading-tight">
                        <div class="text-sm font-medium text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500 truncate max-w-[160px]">{{ auth()->user()->email }}</div>
                    </div>

                    {{-- Avatar button --}}
                    <button @click="open = !open"
                        class="h-9 w-9 md:h-10 md:w-10 rounded-full
                               bg-gradient-to-br from-indigo-600 to-indigo-700
                               text-white font-bold shadow-md
                               hover:scale-105 active:scale-95 transition
                               flex items-center justify-center text-sm md:text-base
                               flex-shrink-0 touch-manipulation">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="open"
                         x-cloak
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 top-11 md:top-12 w-56 bg-white
                                rounded-2xl shadow-xl border border-gray-100 py-2 z-[60]">

                        {{-- Infos user (toujours visible dans le dropdown) --}}
                        <div class="px-4 py-2 border-b border-gray-100 mb-1">
                            <div class="text-sm font-semibold text-gray-800 truncate">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</div>
                        </div>

                        <a href="{{ route('profile') }}"
                           class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <x-heroicon-o-user class="w-4 h-4 text-gray-400"/>
                            Profil
                        </a>

                        <div class="border-t border-gray-100 my-1"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                <x-heroicon-o-arrow-left-on-rectangle class="w-4 h-4"/>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
                @endauth

            </div>
        </header>

        {{-- ================= CONTENT ================= --}}
        <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                {{ $slot }}

                <div class="text-center text-xs text-gray-400 mt-12 md:mt-16 pb-4">
                    {{ config('app.vendor.copyright') }} — Version {{ config('app.product_version') }}
                </div>
            </div>
        </main>

    </div>
</div>

{{-- ================= TOAST SUCCESS ================= --}}
@if(session('success'))
<div x-data="{ show: true }"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-init="setTimeout(() => show = false, 4000)"
     class="fixed top-4 right-4 md:top-6 md:right-6 z-[100]
            max-w-[calc(100vw-2rem)] md:max-w-sm
            bg-green-500 text-white px-4 md:px-5 py-3 rounded-2xl shadow-xl">
    <div class="flex items-center gap-3">
        <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0"/>
        <span class="font-semibold text-sm flex-1">{{ session('success') }}</span>
        <button @click="show = false" class="opacity-70 hover:opacity-100 flex-shrink-0 ml-1">
            <x-heroicon-o-x-mark class="w-4 h-4"/>
        </button>
    </div>
</div>
@endif

{{-- ================= TOAST ERROR ================= --}}
@if(session('error'))
<div x-data="{ show: true }"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-init="setTimeout(() => show = false, 5000)"
     class="fixed top-4 right-4 md:top-6 md:right-6 z-[100]
            max-w-[calc(100vw-2rem)] md:max-w-sm
            bg-red-500 text-white px-4 md:px-5 py-3 rounded-2xl shadow-xl">
    <div class="flex items-center gap-3">
        <x-heroicon-o-exclamation-circle class="w-5 h-5 flex-shrink-0"/>
        <span class="font-semibold text-sm flex-1">{{ session('error') }}</span>
        <button @click="show = false" class="opacity-70 hover:opacity-100 flex-shrink-0 ml-1">
            <x-heroicon-o-x-mark class="w-4 h-4"/>
        </button>
    </div>
</div>
@endif

{{-- ================= CALCULATRICE ================= --}}
<div x-data="calculator()" x-init="init()"
     class="fixed bottom-4 right-4 md:bottom-6 md:right-6 z-50">

    <button @click="toggle()"
        class="w-12 h-12 md:w-14 md:h-14 rounded-full
               bg-gradient-to-br from-indigo-600 to-indigo-700
               text-white shadow-xl hover:scale-110 active:scale-95
               transition flex items-center justify-center text-lg md:text-xl
               touch-manipulation">
        🧮
    </button>

    <div x-show="open"
         x-cloak
         x-transition.scale.origin.bottom.right.duration.200ms
         @click.outside="open = false"
         class="absolute bottom-16 right-0 w-64 md:w-72 bg-white rounded-3xl shadow-2xl p-3 md:p-4 border">

        <input type="text"
            x-ref="screen"
            x-model="display"
            readonly
            class="w-full mb-3 text-right text-base md:text-lg font-semibold
                   bg-gray-50 border rounded-xl p-2 md:p-3 focus:outline-none">

        <div class="grid grid-cols-4 gap-1.5 text-sm font-semibold">
            <template x-for="btn in buttons" :key="btn.label">
                <button type="button"
                    @click.stop="handle(btn.value)"
                    :class="btn.class"
                    class="py-3 rounded-xl transition active:scale-95 touch-manipulation">
                    <span x-text="btn.label"></span>
                </button>
            </template>
            <button type="button"
                @click.stop="clear()"
                class="col-span-4 py-3 rounded-xl bg-red-500 text-white hover:bg-red-600 touch-manipulation">
                Effacer
            </button>
        </div>
    </div>
</div>

{{-- ================= SIDEBAR JS ================= --}}
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('app-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const isOpen  = sidebar.classList.toggle('open');
        overlay.classList.toggle('active', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    function closeSidebar() {
        const sidebar = document.getElementById('app-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Fermer sidebar au clic sur un lien (mobile)
        if (window.innerWidth < 1024) {
            document.querySelectorAll('#app-sidebar a').forEach(link => {
                link.addEventListener('click', closeSidebar);
            });
        }
        // Fermer si on resize vers desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) closeSidebar();
        });
    });
</script>

{{-- ================= CALCULATOR JS ================= --}}
<script>
function calculator() {
    return {
        open: false,
        display: '',

        buttons: [
            {label:'7', value:'7', class:'bg-gray-100 hover:bg-gray-200'},
            {label:'8', value:'8', class:'bg-gray-100 hover:bg-gray-200'},
            {label:'9', value:'9', class:'bg-gray-100 hover:bg-gray-200'},
            {label:'÷', value:'/', class:'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'},

            {label:'4', value:'4', class:'bg-gray-100 hover:bg-gray-200'},
            {label:'5', value:'5', class:'bg-gray-100 hover:bg-gray-200'},
            {label:'6', value:'6', class:'bg-gray-100 hover:bg-gray-200'},
            {label:'×', value:'*', class:'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'},

            {label:'1', value:'1', class:'bg-gray-100 hover:bg-gray-200'},
            {label:'2', value:'2', class:'bg-gray-100 hover:bg-gray-200'},
            {label:'3', value:'3', class:'bg-gray-100 hover:bg-gray-200'},
            {label:'−', value:'-', class:'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'},

            {label:'0', value:'0', class:'bg-gray-100 hover:bg-gray-200'},
            {label:'.', value:'.', class:'bg-gray-100 hover:bg-gray-200'},
            {label:'=', value:'=', class:'bg-green-500 text-white hover:bg-green-600'},
            {label:'+', value:'+', class:'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'},
        ],

        init() {
            if (window.__calculatorKeyboardAttached) return;
            window.__calculatorKeyboardAttached = true;
            window.addEventListener('keydown', (e) => {
                if (!this.open) return;
                const k = e.key;
                const allowed = /^[0-9]$/.test(k) ||
                    ['+','-','*','/','.',  'Enter','Backspace','Escape'].includes(k);
                if (!allowed) return;
                e.preventDefault();
                if (/^[0-9]$/.test(k))             return this.handle(k);
                if (['+','-','*','/','.' ].includes(k)) return this.handle(k);
                if (k === 'Enter')    return this.calculate();
                if (k === 'Backspace') this.display = this.display.slice(0, -1);
                if (k === 'Escape')    this.open = false;
            });
        },

        toggle() {
            this.open = !this.open;
            this.$nextTick(() => this.$refs.screen?.focus());
        },

        handle(val) {
            if (val === '=') { this.calculate(); return; }
            const ops  = ['+','-','*','/'];
            const last = this.display.slice(-1);
            if (!this.display && ops.includes(val)) return;
            if (ops.includes(last) && ops.includes(val)) return;
            if (val === '.' && /(\.\d*)$/.test(this.display)) return;
            this.display += val;
        },

        clear() { this.display = ''; },

        calculate() {
            if (!this.display) return;
            try {
                if (!/^[0-9+\-*/.() ]+$/.test(this.display)) throw 'invalid';
                const result = this.evaluateExpression(this.display);
                if (!isFinite(result)) throw 'math';
                this.display = Number(result.toFixed(8)).toString();
            } catch {
                this.display = 'Erreur';
            }
        },

        evaluateExpression(expr) {
            const tokens     = expr.match(/(\d+(\.\d+)?)|[+\-*/()]/g);
            const precedence = {'+':1, '-':1, '*':2, '/':2};
            const output     = [];
            const operators  = [];

            tokens.forEach(token => {
                if (!isNaN(token)) {
                    output.push(parseFloat(token));
                } else if (token in precedence) {
                    while (operators.length && precedence[operators.at(-1)] >= precedence[token])
                        output.push(operators.pop());
                    operators.push(token);
                } else if (token === '(') {
                    operators.push(token);
                } else if (token === ')') {
                    while (operators.at(-1) !== '(') output.push(operators.pop());
                    operators.pop();
                }
            });

            while (operators.length) output.push(operators.pop());

            const stack = [];
            output.forEach(token => {
                if (typeof token === 'number') {
                    stack.push(token);
                } else {
                    const b = stack.pop(), a = stack.pop();
                    switch (token) {
                        case '+': stack.push(a + b); break;
                        case '-': stack.push(a - b); break;
                        case '*': stack.push(a * b); break;
                        case '/':
                            if (b === 0) throw 'division';
                            stack.push(a / b);
                            break;
                    }
                }
            });

            return stack[0];
        }
    };
}
</script>

@livewireScripts
@stack('scripts')

</body>
</html>