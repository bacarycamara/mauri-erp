<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $company = auth()->check() ? company() : null; @endphp
    <title>{{ config('app.vendor.name') }} — {{ $company?->name ?? config('app.name') }}</title>
    <link rel="icon" href="{{ asset(config('app.vendor.logo')) }}">
    <link rel="apple-touch-icon" href="{{ asset(config('app.vendor.logo')) }}">
    <meta name="theme-color" content="#312e81">
    <meta name="application-name" content="{{ config('app.vendor.name') }}">
    <meta name="author" content="Bacary Camara">
    <meta name="description" content="{{ config('app.vendor.name') }} - ERP Professionnel moderne">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }
        #sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:40; backdrop-filter:blur(2px); }
        #sidebar-overlay.active { display:block; }
        #app-sidebar { transition:transform .28s cubic-bezier(.4,0,.2,1); flex-shrink:0; }
        @media (max-width:1023px) {
            #app-sidebar { position:fixed!important; top:0; left:0; height:100dvh; z-index:50; transform:translateX(-100%); }
            #app-sidebar.open { transform:translateX(0); box-shadow:4px 0 30px rgba(0,0,0,.25); }
        }
        .profile-dropdown { position:absolute; top:calc(100% + 10px); right:0; width:224px; z-index:9999; }
        #app-header { overflow:visible!important; position:relative; z-index:100; }
        .table-responsive-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; border-radius:12px; }
        @media (max-width:767px) {
            .table-erp.table-cards thead { display:none; }
            .table-erp.table-cards tbody tr { display:block; border-bottom:1px solid #e5e7eb; padding:12px 16px; }
            .table-erp.table-cards tbody td { display:flex; justify-content:space-between; align-items:center; border:none; padding:5px 0; border-bottom:1px solid #f8fafc; font-size:.82rem; }
            .table-erp.table-cards tbody td:last-child { border-bottom:none; }
            .table-erp.table-cards tbody td::before { content:attr(data-label); font-weight:600; color:#6b7280; font-size:.72rem; text-transform:uppercase; letter-spacing:.4px; flex-shrink:0; margin-right:12px; }
            .table-erp.table-cards tbody td.no-label::before { display:none; }
        }
        main::-webkit-scrollbar { width:6px; }
        main::-webkit-scrollbar-track { background:transparent; }
        main::-webkit-scrollbar-thumb { background:#c7d2fe; border-radius:99px; }
        main::-webkit-scrollbar-thumb:hover { background:#a5b4fc; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-800">
<div id="sidebar-overlay" onclick="closeSidebar()"></div>
<div class="flex h-screen overflow-hidden">
    <div id="app-sidebar">@include('layouts.navigation')</div>
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden min-h-0">
        <header id="app-header" class="bg-white/80 backdrop-blur border-b border-gray-200 shadow-sm flex-shrink-0">
            <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4 gap-3">
                <div class="flex items-center gap-3 md:gap-5 min-w-0 flex-1">
                    <button onclick="toggleSidebar()" class="lg:hidden flex-shrink-0 p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition text-gray-600 touch-manipulation">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>
                    <img src="{{ asset(config('app.vendor.logo')) }}" alt="{{ config('app.vendor.name') }}" class="h-8 md:h-9 object-contain flex-shrink-0">
                    <div class="h-6 w-px bg-gray-200 flex-shrink-0 hidden sm:block"></div>
                    @if($company)
                    <div class="flex items-center gap-2 min-w-0 flex-shrink-0">
                        @if($company->logo_url)
                        <img src="{{ $company->logo_url }}" alt="{{ e($company->name) }}" class="h-7 md:h-8 object-contain flex-shrink-0 hidden sm:block">
                        @endif
                        <div class="leading-tight min-w-0 hidden sm:block">
                            <div class="text-sm font-semibold text-gray-800 truncate max-w-[140px]">{{ e($company->name) }}</div>
                            <div class="text-xs text-gray-500">{{ e($company->currency) }}</div>
                        </div>
                        <div class="sm:hidden leading-tight">
                            <div class="text-xs font-semibold text-gray-700 truncate max-w-[80px]">{{ e($company->name) }}</div>
                        </div>
                    </div>
                    <div class="h-6 w-px bg-gray-200 flex-shrink-0 hidden md:block"></div>
                    @endif
                    <div class="flex items-center gap-2 min-w-0">
                        <x-heroicon-o-squares-2x2 class="w-4 h-4 md:w-5 md:h-5 text-indigo-600 flex-shrink-0"/>
                        <h1 class="text-sm md:text-base font-semibold text-gray-800 tracking-wide truncate">{{ $title ?? '' }}</h1>
                    </div>
                </div>
                @auth
                <div x-data="{ open: false }" class="relative flex items-center gap-2 md:gap-3 flex-shrink-0 overflow-visible">
                    <div class="hidden md:block text-right leading-tight">
                        <div class="text-sm font-medium text-gray-800">{{ e(auth()->user()->name) }}</div>
                        <div class="text-xs text-gray-500 truncate max-w-[160px]">{{ e(auth()->user()->email) }}</div>
                    </div>
                    <button @click.stop="open = !open" class="h-9 w-9 md:h-10 md:w-10 rounded-full bg-gradient-to-br from-indigo-600 to-indigo-700 text-white font-bold shadow-md hover:scale-105 active:scale-95 transition flex items-center justify-center text-sm md:text-base flex-shrink-0 touch-manipulation">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false"
                         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="profile-dropdown bg-white rounded-2xl shadow-xl border border-gray-100 py-2">
                        <div class="px-4 py-2 border-b border-gray-100 mb-1">
                            <div class="text-sm font-semibold text-gray-800 truncate">{{ e(auth()->user()->name) }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ e(auth()->user()->email) }}</div>
                        </div>
                        <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <x-heroicon-o-user class="w-4 h-4 text-gray-400"/> Profil
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                <x-heroicon-o-arrow-left-on-rectangle class="w-4 h-4"/> Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </header>
        <main class="flex-1 overflow-y-auto min-h-0 p-4 md:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                {{ $slot }}
                <div class="text-center text-xs text-gray-400 mt-12 md:mt-16 pb-4">
                    {{ config('app.vendor.copyright') }} — Version {{ config('app.product_version') }}
                </div>
            </div>
        </main>
    </div>
</div>

@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-cloak x-init="setTimeout(() => show = false, 4000)"
     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed top-4 right-4 md:top-6 md:right-6 z-[300] max-w-[calc(100vw-2rem)] md:max-w-sm bg-green-500 text-white px-4 md:px-5 py-3 rounded-2xl shadow-xl">
    <div class="flex items-center gap-3">
        <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0"/>
        <span class="font-semibold text-sm flex-1">{{ session('success') }}</span>
        <button @click="show = false" class="opacity-70 hover:opacity-100 flex-shrink-0 ml-1"><x-heroicon-o-x-mark class="w-4 h-4"/></button>
    </div>
</div>
@endif

@if(session('error'))
<div x-data="{ show: true }" x-show="show" x-cloak x-init="setTimeout(() => show = false, 5000)"
     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     class="fixed top-4 right-4 md:top-6 md:right-6 z-[300] max-w-[calc(100vw-2rem)] md:max-w-sm bg-red-500 text-white px-4 md:px-5 py-3 rounded-2xl shadow-xl">
    <div class="flex items-center gap-3">
        <x-heroicon-o-exclamation-circle class="w-5 h-5 flex-shrink-0"/>
        <span class="font-semibold text-sm flex-1">{{ session('error') }}</span>
        <button @click="show = false" class="opacity-70 hover:opacity-100 flex-shrink-0 ml-1"><x-heroicon-o-x-mark class="w-4 h-4"/></button>
    </div>
</div>
@endif

<div x-data="calculator()" x-init="init()" class="fixed bottom-4 right-4 md:bottom-6 md:right-6 z-50">
    <button @click="toggle()" class="w-12 h-12 md:w-14 md:h-14 rounded-full bg-gradient-to-br from-indigo-600 to-indigo-700 text-white shadow-xl hover:scale-110 active:scale-95 transition flex items-center justify-center text-lg md:text-xl touch-manipulation">🧮</button>
    <div x-show="open" x-cloak x-transition.scale.origin.bottom.right.duration.200ms @click.outside="open = false"
         class="absolute bottom-16 right-0 w-64 md:w-72 bg-white rounded-3xl shadow-2xl p-3 md:p-4 border">
        <input type="text" x-ref="screen" x-model="display" readonly class="w-full mb-3 text-right text-base md:text-lg font-semibold bg-gray-50 border rounded-xl p-2 md:p-3 focus:outline-none">
        <div class="grid grid-cols-4 gap-1.5 text-sm font-semibold">
            <template x-for="btn in buttons" :key="btn.label">
                <button type="button" @click.stop="handle(btn.value)" :class="btn.class" class="py-3 rounded-xl transition active:scale-95 touch-manipulation"><span x-text="btn.label"></span></button>
            </template>
            <button type="button" @click.stop="clear()" class="col-span-4 py-3 rounded-xl bg-red-500 text-white hover:bg-red-600 touch-manipulation">Effacer</button>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    const s=document.getElementById('app-sidebar'), o=document.getElementById('sidebar-overlay');
    const isOpen=s.classList.toggle('open');
    o.classList.toggle('active',isOpen);
    document.body.style.overflow=isOpen?'hidden':'';
}
function closeSidebar() {
    const s=document.getElementById('app-sidebar'), o=document.getElementById('sidebar-overlay');
    s.classList.remove('open'); o.classList.remove('active'); document.body.style.overflow='';
}
document.addEventListener('DOMContentLoaded',function(){
    if(window.innerWidth<1024) document.querySelectorAll('#app-sidebar a').forEach(l=>l.addEventListener('click',closeSidebar));
    window.addEventListener('resize',function(){ if(window.innerWidth>=1024) closeSidebar(); });
});
</script>

<script>
function calculator(){return{open:false,display:'',buttons:[{label:'7',value:'7',class:'bg-gray-100 hover:bg-gray-200'},{label:'8',value:'8',class:'bg-gray-100 hover:bg-gray-200'},{label:'9',value:'9',class:'bg-gray-100 hover:bg-gray-200'},{label:'÷',value:'/',class:'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'},{label:'4',value:'4',class:'bg-gray-100 hover:bg-gray-200'},{label:'5',value:'5',class:'bg-gray-100 hover:bg-gray-200'},{label:'6',value:'6',class:'bg-gray-100 hover:bg-gray-200'},{label:'×',value:'*',class:'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'},{label:'1',value:'1',class:'bg-gray-100 hover:bg-gray-200'},{label:'2',value:'2',class:'bg-gray-100 hover:bg-gray-200'},{label:'3',value:'3',class:'bg-gray-100 hover:bg-gray-200'},{label:'−',value:'-',class:'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'},{label:'0',value:'0',class:'bg-gray-100 hover:bg-gray-200'},{label:'.',value:'.',class:'bg-gray-100 hover:bg-gray-200'},{label:'=',value:'=',class:'bg-green-500 text-white hover:bg-green-600'},{label:'+',value:'+',class:'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'}],
init(){if(window.__calcKbd)return;window.__calcKbd=true;window.addEventListener('keydown',(e)=>{if(!this.open)return;const k=e.key;const ok=/^[0-9]$/.test(k)||['+','-','*','/','.',  'Enter','Backspace','Escape'].includes(k);if(!ok)return;e.preventDefault();if(/^[0-9]$/.test(k))return this.handle(k);if(['+','-','*','/','.' ].includes(k))return this.handle(k);if(k==='Enter')return this.calculate();if(k==='Backspace')this.display=this.display.slice(0,-1);if(k==='Escape')this.open=false;});},
toggle(){this.open=!this.open;this.$nextTick(()=>this.$refs.screen?.focus());},
handle(v){if(v==='='){this.calculate();return;}const ops=['+','-','*','/'];const l=this.display.slice(-1);if(!this.display&&ops.includes(v))return;if(ops.includes(l)&&ops.includes(v))return;if(v==='.'&&/(\.\d*)$/.test(this.display))return;this.display+=v;},
clear(){this.display='';},
calculate(){if(!this.display)return;try{if(!/^[0-9+\-*/.() ]+$/.test(this.display))throw'x';const r=this.eval(this.display);if(!isFinite(r))throw'x';this.display=Number(r.toFixed(8)).toString();}catch{this.display='Erreur';}},
eval(e){const t=e.match(/(\d+(\.\d+)?)|[+\-*/()]/g),p={'+':1,'-':1,'*':2,'/':2},o=[],op=[];t.forEach(tk=>{if(!isNaN(tk)){o.push(parseFloat(tk));}else if(tk in p){while(op.length&&p[op.at(-1)]>=p[tk])o.push(op.pop());op.push(tk);}else if(tk==='('){op.push(tk);}else if(tk===')'){while(op.at(-1)!=='(')o.push(op.pop());op.pop();}});while(op.length)o.push(op.pop());const s=[];o.forEach(tk=>{if(typeof tk==='number'){s.push(tk);}else{const b=s.pop(),a=s.pop();switch(tk){case'+':s.push(a+b);break;case'-':s.push(a-b);break;case'*':s.push(a*b);break;case'/':if(b===0)throw'x';s.push(a/b);break;}}});return s[0];}
};}
</script>

@livewireScripts
@stack('scripts')
</body>
</html>