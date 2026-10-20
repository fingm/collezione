<nav x-data="{ open: false }" class="bg-slate-900 border-b border-slate-800 text-slate-100 shadow-md">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg p-1 transition-all">
                        <x-application-logo class="block h-9 w-auto fill-current text-indigo-400 hover:text-indigo-300 transition-colors" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 sm:-my-px sm:flex items-center">
                    <x-nav-link 
                        :href="route('dashboard')" 
                        :active="request()->routeIs('dashboard')"
                        class="text-xs font-bold uppercase tracking-wider px-3 py-2 rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-indigo-600/20 text-indigo-400 border-b-2 border-indigo-500' : 'text-slate-300 hover:text-slate-100 hover:bg-slate-800/60' }}">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link 
                        :href="route('productos.index')" 
                        :active="request()->routeIs('productos.*')"
                        class="text-xs font-bold uppercase tracking-wider px-3 py-2 rounded-lg transition-all {{ request()->routeIs('productos.*') ? 'bg-indigo-600/20 text-indigo-400 border-b-2 border-indigo-500' : 'text-slate-300 hover:text-slate-100 hover:bg-slate-800/60' }}">
                        {{ __('Productos') }}
                    </x-nav-link>
                    <x-nav-link 
                        :href="route('ventas.index')" 
                        :active="request()->routeIs('ventas.*')"
                        class="text-xs font-bold uppercase tracking-wider px-3 py-2 rounded-lg transition-all {{ request()->routeIs('ventas.*') ? 'bg-indigo-600/20 text-indigo-400 border-b-2 border-indigo-500' : 'text-slate-300 hover:text-slate-100 hover:bg-slate-800/60' }}">
                        {{ __('Ventas') }}
                    </x-nav-link>
                    <x-nav-link 
                        :href="route('usuarios.index')" 
                        :active="request()->routeIs('usuarios.*')"
                        class="text-xs font-bold uppercase tracking-wider px-3 py-2 rounded-lg transition-all {{ request()->routeIs('usuarios.*') ? 'bg-indigo-600/20 text-indigo-400 border-b-2 border-indigo-500' : 'text-slate-300 hover:text-slate-100 hover:bg-slate-800/60' }}">
                        {{ __('Usuarios') }}
                    </x-nav-link>
                    <x-nav-link 
                        :href="route('envios.index')" 
                        :active="request()->routeIs('envios.*')"
                        class="text-xs font-bold uppercase tracking-wider px-3 py-2 rounded-lg transition-all {{ request()->routeIs('envios.*') ? 'bg-indigo-600/20 text-indigo-400 border-b-2 border-indigo-500' : 'text-slate-300 hover:text-slate-100 hover:bg-slate-800/60' }}">
                        {{ __('Envios') }}
                    </x-nav-link>
                    <x-nav-link 
                        :href="route('gastos.index')" 
                        :active="request()->routeIs('gastos.*')"
                        class="text-xs font-bold uppercase tracking-wider px-3 py-2 rounded-lg transition-all {{ request()->routeIs('gastos.*') ? 'bg-indigo-600/20 text-indigo-400 border-b-2 border-indigo-500' : 'text-slate-300 hover:text-slate-100 hover:bg-slate-800/60' }}">
                        {{ __('Gastos') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-slate-700/80 text-xs font-bold rounded-lg text-slate-200 bg-slate-800/80 hover:bg-slate-800 hover:text-white focus:outline-none transition-all cursor-pointer">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span>{{ Auth::user()->name }}</span>
                            </div>

                            <div class="ms-2">
                                <svg class="fill-current h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="bg-slate-800 border border-slate-700 rounded-xl shadow-xl overflow-hidden py-1">
                            <x-dropdown-link :href="route('profile.edit')" class="text-xs font-medium text-slate-200 hover:bg-slate-700/70 hover:text-white transition-colors">
                                {{ __('Perfil') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();"
                                        class="text-xs font-medium text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors">
                                    {{ __('Cerrar Sesión') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800 focus:outline-none transition-colors">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-slate-900 border-t border-slate-800">
        <div class="pt-2 pb-3 space-y-1 px-2">
            <x-responsive-nav-link 
                :href="route('dashboard')" 
                :active="request()->routeIs('dashboard')"
                class="text-xs font-bold uppercase rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-600/30 text-indigo-400' : 'text-slate-300 hover:bg-slate-800' }}">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link 
                :href="route('productos.index')" 
                :active="request()->routeIs('productos.*')"
                class="text-xs font-bold uppercase rounded-lg {{ request()->routeIs('productos.*') ? 'bg-indigo-600/30 text-indigo-400' : 'text-slate-300 hover:bg-slate-800' }}">
                {{ __('Productos') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link 
                :href="route('ventas.index')" 
                :active="request()->routeIs('ventas.*')"
                class="text-xs font-bold uppercase rounded-lg {{ request()->routeIs('ventas.*') ? 'bg-indigo-600/30 text-indigo-400' : 'text-slate-300 hover:bg-slate-800' }}">
                {{ __('Ventas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link 
                :href="route('usuarios.index')" 
                :active="request()->routeIs('usuarios.*')"
                class="text-xs font-bold uppercase rounded-lg {{ request()->routeIs('usuarios.*') ? 'bg-indigo-600/30 text-indigo-400' : 'text-slate-300 hover:bg-slate-800' }}">
                {{ __('Usuarios') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link 
                :href="route('envios.index')" 
                :active="request()->routeIs('envios.*')"
                class="text-xs font-bold uppercase rounded-lg {{ request()->routeIs('envios.*') ? 'bg-indigo-600/30 text-indigo-400' : 'text-slate-300 hover:bg-slate-800' }}">
                {{ __('Envios') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link 
                :href="route('gastos.index')" 
                :active="request()->routeIs('gastos.*')"
                class="text-xs font-bold uppercase rounded-lg {{ request()->routeIs('gastos.*') ? 'bg-indigo-600/30 text-indigo-400' : 'text-slate-300 hover:bg-slate-800' }}">
                {{ __('Gastos') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-slate-800 px-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <div>
                    <div class="font-bold text-sm text-slate-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-xs text-slate-400">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-xs font-medium text-slate-300 hover:bg-slate-800 rounded-lg">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();"
                            class="text-xs font-medium text-red-400 hover:bg-red-500/10 rounded-lg">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>