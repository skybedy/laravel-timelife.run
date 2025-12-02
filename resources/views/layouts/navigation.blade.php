
<nav x-data="{ open: false }" class="border-b border-orange-400 bg-[#fefdf9] py-4">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="flex justify-between h-16">

                <!-- Logo -->
                <div class="shrink-0 flex items-center flex-1">
                    <a href="{{ route('index') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-80"  width="70px" height="70px" />
                    </a>
                    <div class="pt-1">
                        <div class="indie-flower-regular text-3xl sm:text-4xl">LifeRun</div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 lg:space-x-8 sm:-my-px sm:ms-10 md:flex bg-red-70">
                    <x-nav-link :href="route('index')" :active="request()->routeIs('index')" class="hidden text-lg lg:text-xl text-gray-600">
                        {{ __('Hlavní strana') }}
                    </x-nav-link>

                    <!--
                     <x-nav-link :href="route('event.index')" :active="request()->routeIs('event.index')" class="text-lg lg:text-xl text-gray-600">
                        {{ __('Závody') }}
                    </x-nav-link>
                -->

                    <x-nav-link :href="route('how_it_works.index')" :active="request()->routeIs('how_it_works.index')" class="hidden text-lg lg:text-xl text-gray-600">
                        {{ __('Jak na to') }}
                    </x-nav-link>

                    <x-nav-link :href="route('about.index')" :active="request()->routeIs('about.index')" class="hidden text-lg lg:text-xl text-gray-600">
                        {{ __('O platformě') }}
                    </x-nav-link>
                </div>

            <!-- Settings Dropdown -->
            <div class="hidden">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        @auth

                        <button class="inline-flex items-center px-3 pt-[0.4rem] border border-transparent text-base lg:text-lg leading-4 font-medium rounded-md text-blue-600  hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</div>
                        </button>
                      @else
                         <a class="border-solid border border-red-600 hover:bg-red-700 hover:text-white text-red-700 py-2 px-4 rounded" href="{{ route('login') }}">Přihlásit se</a>
                      @endif


                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profil') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('result.manage')">
                            {{ __('Správa výsledků') }}
                        </x-dropdown-link>
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Odhlásit se') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="hidden flex items-center md:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center  rounded-md text-white hover:white hover:bg-gray-800 focus:outline-none focus:bg-gray-800 focus:text-white transition duration-150 ease-in-out bg-gray-800 mr-1">
                    <svg class="h-10 w-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">

        <div class="hidden pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('index')" :active="request()->routeIs('index')">
                {{ __('Hlavní strana') }}
            </x-responsive-nav-link>
        </div>

        <div class="hidden pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('how_it_works.index')" :active="request()->routeIs('how_it_works.index')">
                {{ __('Jak na to') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="hidden pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
              @auth
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</div>
               @else
                    <a class="border-solid border border-red-600 hover:bg-red-700 hover:text-white text-red-700 py-2 px-4 rounded" href="{{ route('login') }}">Přihlásit se</a>
              @endif
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profil') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('result.manage')">
                      {{ __('Správa výsledků') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Odhlásit se') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
