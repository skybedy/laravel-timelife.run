@inject('carbon', 'Carbon\Carbon')

@section('title', '| Hlavní strana')

<x-app-layout>
    <div class="pb-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

       
                    <!-- Hlavní event box -->
                    <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">

                        <!-- Vnitřní šedý box -->
                        <div class="bg-[#374151] rounded-2xl overflow-hidden">

                            <!-- Fotka -->
                            <div class="w-full relative rounded-t-2xl">
                                <img src="{{ asset('images/jitka-logo.png') }}"
                                     alt="{{ $events[0]->name }}"
                                     class="w-full h-auto object-contain shadow-inner rounded-t-2xl">
                            </div>

                            <!-- Bílá linka pod fotkou -->
                            <div class="border-t-4 border-white"></div>

                            <!-- Název závodu -->
                            <div class="text-white text-center font-black bg-gray-700 lg:px-6 py-2 sm:py-8">
                                
                                <div class="text-5xl sm:text-6xl md:text-7xl lg:text-7xl sm:mt-10 sm:pb-4">

                                    <p class="text-4xl xs:text-5xl sm:text-6xl md:text-[5.1rem] lg:text-[6.5rem] xl:text-[7.5rem] 2xl:text-9xl relative pb-1 sm:pb-2 inline-block mb-1 sm:mb-2">
                                        Jitka Dvořáčková
                                        <span class="absolute bottom-0 left-0 right-0 h-[0.2rem] sm:h-1 bg-white"></span>
                                    </p>

                                    <p class="text-xl xs:text-2xl sm:text-6xl  md:text-[2.7rem] lg:text-[3.5rem] xl:text-[4rem] 2xl:text-7xl">100 půmaratonů za 100 dní</p>

                                </div>

                                <!-- Logo Dům pro Julii -->
                                <div class="flex justify-center sm:my-6">
                                    <a href="https://www.dumprojulii.com/" target="_blank" rel="noopener noreferrer" class="transition-opacity hover:opacity-80">
                                        <img src="{{ asset('images/dum-pro-julii-logo-white.png') }}" alt="Dům pro Julii" class="h-36 sm:h-48 md:h-60 w-auto">
                                    </a>
                                </div>

                                <div class="text-[0.63rem] xs:text-[0.98rem] sm:text-lg md:text-2xl lg:text-[2.2rem] xl:text-[2.8rem] text-white font-serif italic flex items-center justify-center gap-1 sm:gap-3">
                                    <svg class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Charitativní akce pro dětský hospic <a href="https://www.dumprojulii.com/" target="_blank" rel="noopener noreferrer" class="underline hover:text-gray-200 transition-colors">Dům pro Julii</a></span>
                                    <svg class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                </div>

                                <!-- Oddělení -->
                                <div class="border-t border-white mt-4 mb-8"></div>

                                <!-- Info údaje -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mx-10 lg:mx-0">

                                    <!-- Začátek běhání -->
                                    <div class="info-box">
                                        <div>
                                            <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <div class="font-bold">
                                                Začátek běhání
                                            </div>
                                        </div>
                                        <div>
                                            26. 10. 25
                                        </div>
                                    </div>

                                    <!-- Konec běhání -->
                                    <div class="info-box">
                                        <div>
                                            <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <div class="font-bold">
                                                Konec běhání
                                            </div>
                                        </div>
                                        <div>
                                            2. 2. 26
                                        </div>
                                    </div>

                                    <!-- Počet půlmaratonů -->
                                    <div class="info-box">
                                        <div>
                                            <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                            </svg>
                                            <div class="font-bold">
                                                Počet půlmaratonů
                                            </div>
                                        </div>
                                        <div>
                                            {{ $totalRaces }}/100
                                        </div>
                                    </div>

                                    <!-- Celkový počet km -->
                                    <div class="info-box">
                                        <div>
                                            <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                            </svg>
                                            <div class="font-bold">
                                                Celková vzdálenost
                                            </div>
                                        </div>
                                        <div>
                                            {{ $totalKm }} km
                                        </div>
                                    </div>

                                    <!-- Celkový čas -->
                                    <div class="info-box">
                                        <div>
                                            <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <div class="font-bold">
                                                Celkový čas
                                            </div>
                                        </div>
                                        <div>
                                            {{ $totalTime }}
                                        </div>
                                    </div>

                                    <!-- Průměrné tempo -->
                                    <div class="info-box">
                                        <div>
                                            <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            <div class="font-bold">
                                                Průměrné tempo
                                            </div>
                                        </div>
                                        <div>
                                            {{ $avgPace }}/km
                                        </div>
                                    </div>



                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- Box s platbou -->
                    <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl mt-4 mb-4 p-4 sm:mt-8 sm:mb-8 sm:p-8">

                        <!-- Šedý box s textem a formulářem -->
                        <div class="bg-gray-700 rounded-2xl p-6 sm:p-8">

                            <!-- Motivační text -->
                            <div class="text-white text-lg sm:text-xl leading-relaxed mb-6 space-y-4">
                                <p class="flex items-start gap-3">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-red-400 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>I ta nejmenší částka má svůj dopad, naše běžecká komunita jen ve FB skupině Běžci běžcům čítá téměř 70.000 lidí a pokud by jen cca 2% z nás věnovalo zcela zanedbatelných 50Kč, Jitka vybere prostřednictvím tohoto kanálu okolo 50.000 Kč na věc, jejíž existenci si pravděpodobně většina z nás nechce ani příliš představovat.</span>
                                </p>

                                <!-- Oddělení mezi odstavci -->
                                <div class="border-t border-white/20 my-4"></div>

                                <p class="flex items-start gap-3">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-green-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <span>Kromě nezbytných poplatků platebnímu operátorovi jdou veškeré prostředky jeho prostřednictvím na účet <strong>2101782768/2010</strong> organizace Dům pro Julii, a jak hlavní protagonistka Jitka Dvořáčková, tak provozovatel této webové stránky si z ní nenechávají ani haléř.</span>
                                </p>
                            </div>

                            <!-- Oddělení -->
                            <div class="border-t border-white/30 my-6"></div>

                            <!-- Platební formulář - Zjednodušený -->
                            <div class="w-full">
                                <form action="{{ route('donation.payment-selection') }}" method="GET">
                                    <!-- Částka -->
                                    <div class="flex flex-col lg:flex-row items-center justify-center gap-4 sm:gap-6 lg:gap-4 xl:gap-8">
                                        <!-- Ikona + Label -->
                                        <div class="flex items-center gap-2 sm:gap-4">
                                            <svg class="w-8 h-8 sm:w-10 sm:h-10 lg:w-10 xl:w-12 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                            <label class="text-xl sm:text-2xl md:text-3xl lg:text-3xl xl:text-4xl text-white font-bold whitespace-nowrap">Podpořit:</label>
                                        </div>

                                        <!-- Input -->
                                        <input
                                            type="number"
                                            name="amount"
                                            min="50"
                                            step="1"
                                            placeholder="Částka v Kč"
                                            required
                                            class="w-full lg:w-56 xl:w-80 px-4 sm:px-8 lg:px-6 xl:px-8 py-3 sm:py-4 rounded-lg text-gray-900 font-bold text-xl sm:text-2xl focus:outline-none focus:ring-2 focus:ring-white"
                                        >

                                        <!-- via Stripe -->
                                        <div class="flex items-center gap-2 sm:gap-4">
                                            <span class="text-white text-base sm:text-lg lg:text-lg xl:text-xl">via</span>
                                            <svg class="h-8 sm:h-10 lg:h-10 xl:h-12" viewBox="0 0 60 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.10 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.36 0 2.72.2 4.09.75v3.88a9.23 9.23 0 0 0-4.1-1.06c-.86 0-1.44.25-1.44.9 0 1.85 6.29.97 6.29 5.88z" fill="#fff"/>
                                            </svg>
                                        </div>

                                        <!-- Tlačítko -->
                                        <button
                                            type="submit"
                                            class="w-full lg:w-auto bg-white text-gray-500 font-bold rounded-lg hover:bg-gray-100 transition-colors text-xl sm:text-2xl lg:text-xl xl:text-2xl px-6 sm:px-10 lg:px-6 xl:px-10 py-3 sm:py-4 whitespace-nowrap"
                                        >
                                            Darovat
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

        </div>
    </div>
</x-app-layout>
